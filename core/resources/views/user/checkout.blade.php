<script src="https://cdn.jsdelivr.net/npm/sweetalert2@9"></script>
@extends('layout.master')

@section('title', 'Checkout')
@section('headertxt', 'Checkout')

@section('content')
<!-- checkout page content area start -->
<div class="checkout-page-content-area">
    <div class="container">
      <form id="billingDetailsForm" action="{{route('user.checkout.transactionStatus')}}" class="checkout-form" method="post">
        <div class="row">
            <div class="col-lg-6">
                <div class="left-content-area">
                    <h3 class="title">Shipping Details</h3>
                    {{-- <form id="billingDetailsForm" action="{{route('user.checkout.placeorder')}}" class="checkout-form" method="post"> --}}
                        {{csrf_field()}}
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-element">
                                    <label>First name <span class="base-color">**</span></label>
                                    <input name="first_name" type="text" id="fname" class="input-field" placeholder="First name..." value="{{$user->shipping_first_name}}">
                                    @if ($errors->has('first_name'))
                                      <p class="text-danger">{{$errors->first('first_name')}}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-element">
                                    <label>Last Name <span class="base-color">**</span></label>
                                    <input name="last_name" type="text" id="lname" class="input-field" placeholder="Last name..." value="{{$user->shipping_last_name}}">
                                    @if ($errors->has('last_name'))
                                      <p class="text-danger">{{$errors->first('last_name')}}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-element">
                                    <label>Phone <span class="base-color">**</span></label>
                                    <input name="phone" id="phone" type="text" class="input-field" placeholder="Phone Number..." value="{{$user->shipping_phone}}">
                                    @if ($errors->has('phone'))
                                      <p class="text-danger">{{$errors->first('phone')}}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="form-element">
                                    <label>Email <span class="base-color">**</span></label>
                                    <input name="email" id="email" type="text" class="input-field" placeholder="Email Address..." value="{{$user->shipping_email}}" required="">
                                    @if ($errors->has('email'))
                                      <p class="text-danger">{{$errors->first('email')}}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="form-element">
                                    <label>Delivery address <span class="base-color">**</span></label>
                                    <input name="address" id="address" type="text" class="input-field" placeholder="Street address..." value="{{$user->address}}" required="">
                                    @if ($errors->has('address'))
                                      <p class="text-danger">{{$errors->first('address')}}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-element select has-icon">
                                    <label>County <span class="base-color">**</span></label>
                                    <select name="country" class="input-field select " required="" id="country">
                                        <option value="" selected disabled>Select County</option>
                                        @foreach ($countries as $country)
                                          <option value="{{$country}}" {{$country==$user->country?'selected':''}}>{{$country}}</option>
                                        @endforeach
                                    </select>
                                    <div class="the-icon">
                                        <i class="fas fa-angle-down"></i>
                                    </div>
                                    @if ($errors->has('country'))
                                      <p class="text-danger">{{$errors->first('country')}}</p>
                                    @endif
                                </div>
                            </div>
                            {{-- <div class="col-lg-6">
                                <div class="form-element">
                                    <label>State <span class="base-color">**</span></label>
                                    <input name="state" type="text" class="input-field" placeholder="Enter state..." value="{{$user->state}}">
                                    @if ($errors->has('state'))
                                      <p class="text-danger">{{$errors->first('state')}}</p>
                                    @endif
                                </div>
                            </div> --}}
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="form-element">
                                    <label>Region <span class="base-color">**</span></label>
                                    <input name="city" id="city" type="text" class="input-field" placeholder="Enter city..." value="{{$user->city}}" required="">
                                    @if ($errors->has('city'))
                                      <p class="text-danger">{{$errors->first('city')}}</p>
                                    @endif
                                </div>
                            </div>
                            {{-- <div class="col-lg-6">
                                <div class="form-element">
                                    <label>Zip Code <span class="base-color">**</span></label>
                                    <input name="zip_code" type="text" class="input-field" placeholder="Enter zip code..." value="{{$user->zip_code}}">
                                    @if ($errors->has('zip_code'))
                                      <p class="text-danger">{{$errors->first('zip_code')}}</p>
                                    @endif
                                </div>
                            </div> --}}
                        </div>


                        <div class="shipping-details">
                            <div class="form-element textarea">
                                <label>Order notes</label>
                                <textarea name="order_notes" class="input-field textarea" cols="30" rows="10"></textarea>
                                <small>Please provide more address details below to help the delivery rider find you faster.maybe the building name, floor and house number </small>
                            </div>
                        </div>
                      </div>
                {{-- </form> --}}
            </div>
            <div class="col-lg-6">
                <div class="right-content-area">
                    <h3 class="title">Your Order</h3>
                    <ul class="order-list">
                        <li>
                            <div class="single-order-list heading">
                                Product Title <span class="right">Total</span>
                            </div>
                        </li>
                        @foreach ($cartItems as $cart)
                          <li class="name" id="li{{$cart->product_id}}">
                              <div class="single-order-list">
                                  <a href="{{route('user.product.details', [$cart->product->slug, $cart->product->id])}}">{{strlen($cart->title) > 50 ? substr($cart->title, 0, 50) . '...' : $cart->title}}</a>
                                  <span class="right">
                                    @if ($cart->current_price)
                                      {{$gs->base_curr_symbol}} {{$cart->current_price*$cart->quantity}}
                                    @else
                                      {{$gs->base_curr_symbol}} {{$cart->price*$cart->quantity}}
                                    @endif
                                  </span>
                              </div>
                          </li>
                        @endforeach

                          <li id="liamount" class="{{$char > 0 ? 'd-block' : 'd-none'}}">
                              <div class="single-order-list title-bold">
                                  Cart Amount <span class="right normal">{{$gs->base_curr_symbol}} {{$amount}}</span>
                              </div>
                          </li>
                          <li id="licoupon" class="{{$char > 0 ? 'd-block' : 'd-none'}}">
                              <div class="single-order-list title-bold">
                                  Coupon Discount <span class="right normal" id="coupon">- {{$gs->base_curr_symbol}} {{$char}}</span>
                              </div>
                          </li>


                      <li>
                          <div class="single-order-list title-bold">
                              Subtotal <span class="right normal" id="subtotal">{{$gs->base_curr_symbol}} {{$amount - $char}}</span>
                          </div>
                      </li>

                        <li class="shipping">
                            <div class="single-order-list title-bold">
                                Shipping Charge
                                <span class="right normal" id="shippingCharge"></span>
                            </div>
                        </li>
                        {{-- <li>
                            <div class="single-order-list title-bold">
                                Tax <span class="right normal">{{$gs->tax}} %</span>
                            </div>
                        </li> --}}
                        <li>
                            <div class="single-order-list title-bold">
                                Total <span class="right normal" id="total">{{$gs->base_curr_symbol}} {{getTotal(Auth::user()->id)}}</span>
                            </div>
                        </li>
                    </ul>
                    <div class="">
                      @if (!empty($cdetails))
                        <div class="alert alert-success" role="alert">
                          Coupon already applied! This coupon code is valid till {{date('jS F, Y', strtotime($cdetails->valid_till))}}
                        </div>
                      @endif
                      {{-- <form id="couponform">
                        {{csrf_field()}}
                        <div class="left-content-area">
                            <div class="coupon-code-wrapper">
                                <div class="form-element" style="margin:0px;">
                                    <input id="couponCodeIn" name="coupon_code" type="text" value="" class="input-field" placeholder="Coupon Code" autocomplete="off">
                                </div>
                                <button type="button" class="submit-btn" onclick="applycoupon(event)">apply coupon</button>
                            </div>
                            <p id="errcouponcode" class="text-danger em"></p>
                        </div>
                      </form> --}}
                      @php
                        if (Auth::check()) {
                          $sessionid = Auth::user()->id;
                        } else {
                          $sessionid = session()->get('browserid');
                        }
                        $pp = \App\PlacePayment::where('cart_id', $sessionid)->first();
                      @endphp

                      <h5>Select a Shipping Method:</h5>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="inlineCheckbox1" name="place" value="in" onchange="calcTotal(document.getElementById('paymentMethod').value)" @if(!empty($pp->place)) {{$pp->place=='in'?'checked':''}} @endif>
                            <label class="form-check-label" for="inlineCheckbox1">In {{$gs->main_city}}</label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="inlineCheckbox2" name="place" value="around" onchange="calcTotal(document.getElementById('paymentMethod').value)" @if(!empty($pp->place)) {{$pp->place=='around'?'checked':''}} @endif>
                            <label class="form-check-label" for="inlineCheckbox2">Within Nairobi</label>
                          </div>
                          <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" id="inlineCheckbox3" name="place" value="world" onchange="calcTotal(document.getElementById('paymentMethod').value)" @if(!empty($pp->place)) {{$pp->place=='world'?'checked':''}} @else checked @endif>
                            <label class="form-check-label" for="inlineCheckbox3">Countrywide</label>
                          </div>
                        </div>
                      </div>
                      <br>

                      <h5>Payment Method:</h5>
                      <div class="row">
                        <div class="col-md-12">
                          <select class="form-control" name="payment_method" id="paymentMethod" onchange="calcTotal(this.value)">
                            {{-- <option value="2" >Pay Now via MPesa</option> --}}
                            <option value="1" >Pay via MPesa</option>
                            {{-- <option value="3" >Pay Now with Visa/ MasterCard</option> --}}
                          </select>
                        </div>
                      </div>
                      <br>
                      <div class="row" id="row_mpesa" style="diplay: none;">

                        <div class="col-md-12">
                            <p>From your phone, 
                              <img src="{{asset('assets/user/img/mpesa.PNG')}}" alt="new collcetion image" width="70" height="40" />
                            </p>

                            <ul>

                                <li>– Go to <b>MPESA</b> Menu</li>
                                <li>– Select <b>Lipa na M-PESA</b></li>
                                <li>– Select <b>Buy Goods and Services</b></li>
                                <li>– Enter <b>Till Number: 5017637</b></li>
                                <li>– Enter <b>Amount: KSh <span id="total2">{{$gs->base_curr_symbol}} {{getTotal(Auth::user()->id)}}</span></b></li>
                                <li>– Enter <b>PIN</b> and confirm transaction</li>
                            </ul>
                            <br>
                              <label class="base-color">Enter MPESA Confirmation Code <span style="color: red;">*</span></label><br>
                            <input type="text" class="form-control col-md-6" name="trans_no" id="trans_no" placeholder="HDTRE6MDJEWO" >
                        </div>
                      </div>
                      <br>
                    </div>
                    <div class="checkbox-element account">
                        <div class="checkbox-wrapper">
                            <label class="checkbox-inner">I’ve read and accepted the <a href="{{route('terms')}}" class="base-color">terms & conditions *</a>
                                <input type="checkbox" name="terms" required="" id="terms">
                                <span class="checkmark"></span>
                            </label>
                            <input type="hidden" name="terms_helper" value="">
                        </div>
                        @if ($errors->has('terms_helper'))
                          <p class="text-danger">{{$errors->first('terms_helper')}}</p>
                        @endif
                    </div>
                    <div class="btn-wrapper">
                        <button type="button" class="submit-btn" onclick="checkoutF()"> place your order </button>
                    </div>
                </div>
            </div>
        </div>
      </form>
    </div>
</div>
</div>
<!-- checkout page content area end -->

@endsection

@section('js-scripts')
  <script>

    $('#paymentMethod').on('change', function(e) {
   
    var option = e.target.value;

    if(option == "1")
    {
      document.getElementById('row_mpesa').style.display = "block";

    }
    else
    {
      document.getElementById('row_mpesa').style.display = "none";
    }


  }); 


    var curr = "{{$gs->base_curr_symbol}}";

    $(document).ready(function() {
      calcTotal(document.getElementById('paymentMethod').value);
    });

    function calcTotal(paymentMethod) {
      if($("#paymentMethod :selected").val() == "1")
      {
        document.getElementById('row_mpesa').style.display = "block";
      }
      else
      {
        document.getElementById('row_mpesa').style.display = "none";
      }
      var place;
      var shippingmethod = document.getElementsByName('place');
      for (var i = 0; i < shippingmethod.length; i++) {
        if (shippingmethod[i].checked) {
          place = shippingmethod[i].value;
        }
      }
      // console.log(place);
      // console.log(paymentMethod);
      $.get(
        '{{route('cart.getTotal')}}',
        {
          place: place,
          paymentMethod: paymentMethod
        },
        function(data) {
          console.log(data);
          $("#shippingCharge").html(curr + " " + data.shippingcharge);
          $("#total").html(curr + " " + data.total);
          $("#total2").html(curr + " " + data.total);
        }
      );
    }

    function applycoupon(e) {
      e.preventDefault();
      var form = document.getElementById('couponform');
      var fd = new FormData();
      fd.append('coupon_code', document.getElementById('couponCodeIn').value);
      $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      $.ajax({
        url: '{{route('user.checkout.applycoupon')}}',
        type: 'POST',
        data: fd,
        contentType: false,
        processData: false,
        success: function(data) {
            console.log(data);
            var em = document.getElementsByClassName('em');
            for (var i = 0; i < em.length; i++) {
              em[i].innerHTML = '';
            }
            if(typeof data.error != 'undefined') {
              if (typeof data.coupon_code != 'undefined') {
                document.getElementById('errcouponcode').innerHTML = data.coupon_code[0];
              }
            } else {
              toastr["success"]("Coupon applied!");
              document.getElementById('couponCodeIn').value = '';
              $("#subtotal").html(curr + " " + data.subtotal);
              $("#total").html(curr + " " + data.total);
              $("#total2").html(curr + " " + data.total);
              if (data.ctotal > 0) {
                $("#licoupon").removeClass('d-none');
                $("#licoupon").addClass('d-block');
                $("#liamount").removeClass('d-none');
                $("#liamount").addClass('d-block');
                $("#coupon").html('- ' + curr + " " + data.ctotal);
              } else {
                $("#licoupon").removeClass('d-block');
                $("#licoupon").addClass('d-none');
                $("#liamount").removeClass('d-block');
                $("#liamount").addClass('d-nonw');
              }

            }
        }
      });
    }

    function checkoutF() {
      
      if($("#paymentMethod :selected").val() == "1")
      {
        if($("#trans_no").val() == "")
        {
          alert('Enter the MPESA Confirmation Code');
          $("#trans_no").focus();

          return false;
        }

        else if($("#fname").val() == "")
        {
          alert('Enter First name');
           $("#fname").focus();

          return false;
        }
        else if($("#lname").val() == "")
        {
          alert('Enter Last name');
           $("#lname").focus();

          return false;
        }
        else if($("#address").val() == "")
        {
          alert('Enter address');
           $("#address").focus();

          return false;
        }
        else if($("#city").val() == "")
        {
          alert('Enter Region');
           $("#city").focus();

          return false;
        }
        else if($("#country :selected").val() == "")
        {
          alert('Select county');
           $("#country").focus();

          return false;
        }
        else if(!$('#terms').is(':checked'))
        {
          alert('Check terms');
           $("#terms").focus();

          return false;
        }
        
      }

      var form = document.getElementById('billingDetailsForm');
      // var fd = new FormData();

      // $.ajaxSetup({
      //     headers: {
      //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      //     }
      // });
      $.ajax({
        url: '{{route('user.checkout.transactionStatus')}}',
        type: 'GET',
        data: {trans_no: $("#trans_no").val()},
        success: function(data) {

            Swal.queue([{
              title: 'Complete',
              confirmButtonText: 'Complete',
              text: 'Click to complete the transaction',
              showLoaderOnConfirm: true,
              preConfirm: () => {
                return $.ajax({
                            url : '{{route('user.checkout.placeorder')}}',
                            type: "POST",
                            data: $('#billingDetailsForm').serialize(),

                            dataType: "JSON",
                            success: function(data)
                            {
                              if(data.error)
                              {
                                Swal.fire({
                                  title: 'Error!',
                                  text: data.error,
                                  icon: 'error',
                                  // closeButtonText: 'No, cancel!',
                                }
                                )
                              }
                              else if(data.success)
                              {
                                  Swal.fire({
                                  title: 'Success!',
                                  text: data.success,
                                  icon: 'success',
                                  // closeButtonText: 'No, cancel!',
                                }
                                ).then((result)=>{
                                  window.location.href =  '{{ route('user.orders') }}';

                                }
                                );

                              }
                                                            },
                            error: function (jqXHR, textStatus, errorThrown)
                            {
                                // alert('Error deleting data');
                                Swal.fire({
                                           title: 'Oops...',
                                            text: 'Error fetching transaction!',
                                            icon: 'error',
                                          })
                            }
                        }) 
              }
            }])
        }
      });
    }

    function placeorder() {
      if($("#paymentMethod :selected").val() == "1")
      {
        if($("#trans_no").val() == "")
        {
          // alert('Enter the MPESA Confirmation Code');
          Swal.fire({
            title: 'Error...',
            text: 'Enter the MPESA Confirmation Code!',
            icon: 'error',
          })
          return false;
        }
        
      }
      document.getElementById("billingDetailsForm").submit();
    }
  </script>
@endsection
