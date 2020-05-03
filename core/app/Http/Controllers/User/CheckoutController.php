<?php

namespace App\Http\Controllers\User;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Order;
use App\Gateway;
use App\Coupon;
use App\Product;
use App\Orderedproduct;
use App\TransQuery;
use App\GeneralSetting as GS;
use Carbon\Carbon;
use Auth;
use App\Cart;
use App\PlacePayment;
use Session;
use Validator;

class CheckoutController extends Controller
{
    public function index() {
      $gs = GS::first();
      if (Auth::check()) {
        $sessionid = Auth::user()->id;
      } else {
        $sessionid = session()->get('browserid');
      }
      if (empty($gs->coupon_code)) {
        if (CartCoupon::where('cart_id', Auth::user()->id)->count() > 0) {
          CartCoupon::where('cart_id', Auth::user()->id)->first()->delete();
        }
      }
      $cartItems = Cart::where('cart_id', Auth::user()->id)->get();
      $data['cartItems'] = $cartItems;
      $amo = 0;
      foreach ($cartItems as $item) {
        if (!empty($item->current_price)) {
          $amo += $item->current_price*$item->quantity;
        } else {
          $amo += $item->price*$item->quantity;
        }
      }

      $char = 0;
      $coupon = Session::get('coupon_code');
      if(isset($coupon) && Coupon::where('coupon_code', $coupon)->count() == 1){
        $cdetails = Coupon::where('coupon_code', $coupon)->latest()->first();
        $data['cdetails'] = $cdetails;
        if ($cdetails->coupon_type == 'percentage'){
          $char = ($amo*$cdetails->coupon_amount)/100;
        }else{
          if($cdetails->coupon_min_amount <= $amo){
            $char = $cdetails->coupon_amount;
          }
        }
      }
      // return $char;
      $data['char']= $char;
      $data['amount']= $amo;



      $data['user'] = User::find(Auth::user()->id);
      $data['countries'] = array("Mombasa", "Nairobi","Bungoma","Kiambu","Kisii","Bomet");
      return view('user.checkout', $data);
    }


    public function couponvaliditycheck() {
      $today = new \Carbon\Carbon(Carbon::now());
      $coupons = Coupon::all();

      foreach ($coupons as $key => $coupon) {
        if ($today->gt(Carbon::parse($coupon->valid_till))) {
          if (session('coupon_code') == $coupon->coupon_code) {
            session()->forget('coupon_code');
          }
          $coupon->delete();
        }
      }


    }


    public function applycoupon(Request $request) {
      $gs = GS::first();

      $cartItems = Cart::where('cart_id', Auth::user()->id)->get();
      $amo = 0;
      foreach ($cartItems as $item) {
        if (!empty($item->current_price)) {
          $amo += $item->current_price*$item->quantity;
        } else {
          $amo += $item->price*$item->quantity;
        }
      }

      $coupon = Coupon::where('coupon_code', $request->coupon_code)->first();

      $validator = Validator::make($request->all(), [
          'coupon_code' => [
              'required',
              function ($attribute, $value, $fail) use ($request, $amo, $coupon) {
                  if (Coupon::where('coupon_code', $request->coupon_code)->count() == 0) {
                    $fail("Coupon code didn't match!");
                  } else {
                    if ($coupon->coupon_type == 'fixed' && $coupon->coupon_min_amount >= $amo) {
                      $fail("Your minimum cart total must be ".$coupon->coupon_min_amount);
                    }
                  }
              },
          ]
      ]);

      if($validator->fails()) {
          // adding an extra field 'error'...
          $validator->errors()->add('error', 'true');
          return response()->json($validator->errors());
      }

      session()->forget('coupon_code');
      session()->put('coupon_code', $request->coupon_code);
      $csession = session('coupon_code');

      $cdetails = Coupon::where('coupon_code', $csession)->first();
      $ctotal = 0;
      if (session()->has('coupon_code') && !empty($cdetails)) {
        if ($cdetails->coupon_type == 'percentage') {
          $ctotal = ($cdetails->coupon_amount * $amo)/100;
        } else {
          $ctotal = $cdetails->coupon_amount;
        }
      }

      $subtotal = getSubTotal(Auth::user()->id);
      $total = getTotal(Auth::user()->id);

      return response()->json(['total'=>$total, 'subtotal'=>$subtotal, 'ctotal'=>$ctotal]);

    }

    public function placeorder(Request $request) {
      $gs = GS::first();

      // return $request;
      $request->validate([
        'first_name' => 'required',
        'last_name' => 'required',
        'phone' => 'required',
        'email' => 'required',
        'address' => 'required',
        'country' => 'required',
        'city' => 'required',
        // 'state' => 'required',
        // 'zip_code' => 'required',
        'terms_helper' => [
          function ($attribute, $value, $fail) use ($request) {
              if (!$request->has('terms')) {
                return $fail('You must accept our terms & conditions');
              }
          },
        ]
      ]);

      if (Cart::where('cart_id', Auth::user()->id)->count() == 0) {
        Session::flash('alert', 'No product added to cart!');
        return back();
      }

      //get the transquery
      $getTransQuery = TransQuery::where('trans_no', $request->input('trans_no'))->where('used',0)->first();
      if($getTransQuery)
      {
        if($getTransQuery->amount < getTotal(Auth::user()->id))
        {
            return response()->json(['error'=>'Insufficient amount '.getTotal(Auth::user()->id)]);
        }

          TransQuery::where('trans_no', $request->input('trans_no'))->update(array('used' => 1));

      }
      else
      {
        return response()->json(['error'=>'MPESA Confirmation Code is invalid']);
      }

      $gs = GS::first();
      // store in order table
      // $in = $request->except('_token', 'coupon_code', 'terms', 'terms_helper');
      $in['user_id'] = Auth::user()->id;
      $in['first_name'] = $request->first_name;
      $in['last_name'] = $request->last_name;
      $in['phone'] = $request->phone;
      $in['email'] = $request->email;
      $in['address'] = $request->address;
      $in['country'] = $request->country;
      $in['state'] = $request->state;
      $in['city'] = $request->city;
      $in['zip_code'] = $request->zip_code;
      $in['order_notes'] = $request->order_notes;
      $in['subtotal'] = getSubTotal(Auth::user()->id);
      $in['total'] = getTotal(Auth::user()->id);
      $in['place'] = $request->place;
      $pm = $request->payment_method;
      $place = $request->place;

      // if payment method is cash on delivery
      if ($pm == 1) {
        if ($place == 'in') {
          $scharge = $gs->in_cash_on_delivery;
        } elseif ($place == 'around') {
          $scharge = $gs->around_cash_on_delivery;
        } else {
          $scharge = $gs->world_cash_on_delivery;
        }
      }
      // if payment method is cash on advance
      else {
        if ($place == 'in') {
          $scharge = $gs->in_advanced;
        } elseif ($place == 'around') {
          $scharge = $gs->around_advanced;
        } else {
          $scharge = $gs->world_advanced;
        }
      }

      $in['shipping_charge'] = $scharge;
      $in['tax'] = $gs->tax;
      $in['payment_method'] = $pm;
      $in['shipping_method'] = $place;
      $order = Order::create($in);
      $order->unique_id = $order->id + 100000;
      $order->save();

      $carts = Cart::where('cart_id', Auth::user()->id)->get();


      // store products in orderedproducts table
      foreach($carts as $cart) {
        $product = Product::select('vendor_id')->where('id', $cart->product_id)->first();
        $op = new Orderedproduct;
        $op->user_id = Auth::user()->id;
        $op->order_id = $order->id;
        $op->vendor_id = $product->vendor_id;
        $op->product_id = $cart->product_id;
        $op->product_name = $cart->title;
        $op->product_price = $cart->price;
        $op->offered_product_price = $cart->current_price;

        $op->attributes = $cart->attributes;

        if (session()->has('coupon_code') && Coupon::where('coupon_code', session('coupon_code'))->count()==1) {
          $csession = session('coupon_code');
          $coupon = Coupon::where('coupon_code', $csession)->first();


          if ($coupon->coupon_type=='percentage') {
            // if coupon type is percentage

            if (empty($cart->current_price)) {
              // if the product has no offer...
              $cartItemTotal = $cart->quantity*$cart->price;
              $cartItemCoupon = ($cartItemTotal*$coupon->coupon_amount)/100;
              $producttotal = $cartItemTotal - $cartItemCoupon;
            } else {
              // if the product has offer...
              $cartItemTotal = $cart->quantity*$cart->current_price;
              $cartItemCoupon = ($cartItemTotal*$coupon->coupon_amount)/100;
              $producttotal = $cartItemTotal - $cartItemCoupon;
            }

          }
          else {
            // if coupon type is fixed

            $cartItems = Cart::where('cart_id', Auth::user()->id)->get();
            $amo = 0;
            foreach ($cartItems as $item) {
              if (!empty($item->current_price)) {
                $amo += $item->current_price*$item->quantity;
              } else {
                $amo += $item->price*$item->quantity;
              }
            }

            $charpertaka = $coupon->coupon_amount/$amo;


            if (empty($cart->current_price)) {
              $cartItemTotal = $cart->quantity*$cart->price;
              $cartItemCoupon = $cartItemTotal*$charpertaka;
              $producttotal = $cartItemTotal-$cartItemCoupon;
            } else {
              $cartItemTotal = $cart->quantity*$cart->current_price;
              $cartItemCoupon = $cartItemTotal*$charpertaka;
              $producttotal = $cartItemTotal-$cartItemCoupon;
            }

          }
        } else {
          if (empty($cart->current_price)) {
            // if cart item has no offer

            $producttotal = $cart->price*$cart->quantity;
            $cartItemCoupon = 0;
          } else {
            // if cart item has offer

            $producttotal = $cart->current_price*$cart->quantity;
            $cartItemCoupon = 0;
          }
        }

        $op->quantity = $cart->quantity;
        $op->product_total = $producttotal;
        $op->coupon_amount = $cartItemCoupon;
        $op->save();
      }


      if ($request->payment_method == 1) {
        // $this->transactionStatus();
        // Session::flash('alert', 'The confirmation code supplied does not exist or is invalid.');
        // return back();    


        // clear coupon from session
        session()->forget('coupon_code');
        // clear cart...
        Cart::where('cart_id', Auth::user()->id)->delete();

        // clear conditions (shipping)...
        PlacePayment::where('cart_id', Auth::user()->id)->delete();

        $message = "Your order has been placed successfully! Our agent will contact with you later. <br><strong>Order ID: </strong> " . $order->unique_id ."<p><strong>Order details: </strong><a href='".url('/')."/".$order->id."/orderdetails'>".url('/')."/".$order->id."/orderdetails"."</a></p>";

        // send_email( $order->user->email, $order->user->first_name, "Order placed", $message);
        // send_email( 'dennis.onkeo81@gmail.com', 'Dennis', "Order placed", $message);
        // send_sms( $order->user->phone, $message);

        // Session::flash('success', 'Order placed successfully! Our agent will contact with you later.');
        // return redirect()->route('user.orders');
        return response()->json(['success'=>'Order placed successfully! Our agent will contact you later']);

      } elseif ($request->payment_method == 2) {
        // redirect to payment gateway page
        return redirect()->route('user.gateways', $order->id);
        // after payment clear Cart and redirect to success page
      }


    }

    public function success() {
      return view('user.order_success');
    }

    public static function generateLiveToken(){
        
        try {
            // $consumer_key = env("MPESA_CONSUMER_KEY");
            // $consumer_key = config('app.MPESA_CONSUMER_KEY');
            $consumer_key = 'i5BT9aAm7xUFmo0P47oaedwg9o7pANLG';
            $consumer_secret = 'mK62M3e2YTp0xBgA';
        } catch (\Throwable $th) {
            // $consumer_key = self::env("MPESA_CONSUMER_KEY");
            $consumer_key = self::config('app.MPESA_CONSUMER_KEY');
            // $consumer_secret = self::env("MPESA_CONSUMER_SECRET");
            $consumer_secret = self::config('app.MPESA_CONSUMER_SECRET');
        }

        if(!isset($consumer_key)||!isset($consumer_secret)){
            die("please declare the consumer key andHHHHH consumer secret as defined in the documentation");
        }
        $url = 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        $credentials = base64_encode($consumer_key.':'.$consumer_secret);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Basic '.$credentials)); //setting a custom header
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $curl_response = curl_exec($curl);

        return json_decode($curl_response)->access_token;


    }

    public function transactionStatus(Request $request)
    {
        
        try {
            $environment = 'live';
        } catch (\Throwable $th) {
            $environment = self::env("MPESA_ENV");
        }
        
        if( $environment =="live"){
            $url = 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query';
            $token=self::generateLiveToken();
        }elseif ($environment=="sandbox"){
            $url = 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query';
            $token=self::generateSandBoxToken();
        }else{
            return json_encode(["Message"=>"invalid application status"]);
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json','Authorization:Bearer '.$token)); //setting custom header


        $curl_post_data = array(
            'Initiator' => 'denno254',
            'SecurityCredential' => 'gqJ8/xdyk8jSBanBosJwrnw1rClRmYxKqULtgMOhXw77XnbN0xDM32u8gnuJvCBF6Hcir2P9sj9JQCmt2EteHXSw8it8KYmGxHy7mVXijf5tKuG7naiucU6goFv6zNtxRsm8irhyDCnZbai/oNo1vTPaF22lvLl3RFmMKxpILCA7p3s5LMXp/y1z481JqGduXzjQtXCbIX4aBDJK4AO2w6ChD7YS4hgsm7AJlzV1oK7ayCzQAVmg8Z2oySOaqVUrW1MwBfclPRR0Sq9nT+tUEFKURj8SUqk6lKynNX+DqaQHqjnDJLwMpHjmguugxtkhchMJuRYy02S/q3NZTWK59Q==',
            'CommandID' => 'TransactionStatusQuery',
            'TransactionID' => $request->input('trans_no'),
            'PartyA' => '5017631',
            'IdentifierType' => '4',
            'ResultURL' => 'https://3d9d07cf.ngrok.io/ecom/api/mpesa-response',
            'QueueTimeOutURL' => 'https://3d9d07cf.ngrok.io/ecom/api/mpesa-response',
            'Remarks' => 'Test',
            'Occasion' => ''
        );

        $data_string = json_encode($curl_post_data);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($curl, CURLOPT_HEADER, false);
        $curl_response = curl_exec($curl);


        // return $curl_response;
        return response()->json('0');
    }


    public function mpesa_response(Request $request)
    {

      // $filename = 'logs.txt';
      // $content = \File::get($filename);

        $callbackJSONData=file_get_contents('php://input');

        $handle=fopen("assets/transactions.txt", 'w');

        fwrite($handle, $callbackJSONData);

        // $account_no = json_decode($callbackJSONData)->Body->stkCallback->MerchantRequestID;

        $amount = json_decode($callbackJSONData)->Result->ResultParameters->ResultParameter[10]->Value;
        $transs_no = json_decode($callbackJSONData)->Result->ResultParameters->ResultParameter[12]->Value;
        $phone = json_decode($callbackJSONData)->Result->ResultParameters->ResultParameter[0]->Value;
        $ResultCode = json_decode($callbackJSONData)->Result->ResultCode;

        if($ResultCode == "0")

        {
          $check = TransQuery::where('trans_no', $transs_no)->first();
          if(!$check)
          {
            $op = new TransQuery();
            $op->user_details = $phone;
            $op->trans_no = $transs_no;
            $op->amount = $amount;
            $op->save();
          }
            
        }


    }
}
