@extends('layout.master')

@section('title', 'Register')

@section('headertxt')
  Signup
@endsection


@section('content')
  <!-- login page content area start -->
  <div class="login-page-content-area">
      <div class="container">
          <div class="row">
              <div class="col-lg-12">
                  @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="padding: 40px;font-size: 20px;">
                      <strong>Success!</strong> {{session('success')}}
                      <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                  @endif

                  <div class="signup-page-wrapper"><!-- login page wrapper -->
                      <div class="or">
                          <span>or</span>
                      </div>
                      <div class="row">
                          <div class="col-lg-6">
                              <div class="left-content-area" style="padding: 80px;">
                                  <div class="top-content">
                                      <h4 class="title">Signup to {{$gs->website_title}}</h4>
                                  </div>
                                  <div class="bottom-content">
                                      {!! $gs->user_register_text !!}

                                  </div>
                                  <p>Already have account? <a style="color:red;font-weight:bold;" href="{{route('login')}}">Click here</a> to login</p>
                              </div>
                          </div>
                          <div class="col-lg-6">
                              <div class="right-contnet-area">
                                  <div class="top-content">
                                      <h4 class="title">Signupdsdsjd Today</h4>
                                  </div>
                                  <div class="bottom-content">
                                      <form action="{{route('user.register')}}" method="post" class="login-form">
                                          {{csrf_field()}}
                                          <div class="form-element">
                                              <input type="text" name="username" class="input-field" value="{{old('username')}}" placeholder="Enter Username" required="">
                                              @if ($errors->has('username'))
                                                <p class="text-danger">{{$errors->first('username')}}</p>
                                              @endif
                                          </div>
                                          <div class="form-element">
                                              <input type="email" name="email" class="input-field" value="{{old('email')}}" placeholder="Enter Email" required="">
                                              @if ($errors->has('email'))
                                                <p class="text-danger">{{$errors->first('email')}}</p>
                                              @endif
                                          </div>
                                          <div class="form-element">
                                              <input type="text" name="phone" class="input-field" value="{{old('phone')}}" placeholder="Enter Phone Number" required="">
                                                <p style="margin-left: 10px;"><small>e.g 254700000000</small></p>
                                              @if ($errors->has('phone'))
                                                <p class="text-danger">{{$errors->first('phone')}}</p>
                                              @endif
                                          </div>
                                          <div class="form-element">
                                              <input type="password" name="password" class="input-field" placeholder="Enter Password" required="">
                                              @if ($errors->has('password'))
                                                <p class="text-danger">{{$errors->first('password')}}</p>
                                              @endif
                                          </div>
                                          <div class="form-element">
                                              <input type="password" name="password_confirmation" class="input-field" placeholder="Enter Password Again">
                                          </div>
                                          <div class="btn-wrapper">
                                              <button type="submit" class="submit-btn">signup</button>
                                              <a href="{{route('login')}}" class="link">Already have account?</a>
                                          </div>
                                          @if (\App\Provider::find(1)->status == 1)
                                            <div class="block-link mt-4">
                                                <a href="{{ url('auth/facebook') }}" class="facebook">join with with facebook</a>
                                            </div>
                                          @endif
                                      </form>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div><!-- //.login page wrapper -->
              </div>
          </div>
      </div>
  </div>
  <!-- login page content area end -->
@endsection
