@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12 col-md-12 mb-30">
            <div class="card">
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label> @lang('Wallet Header Images') </label>
                                    <div class="file-upload-wrapper" data-text="Select your file!">
                                        <input type="file" class="file-upload-field" name="wallet_images[]" id="wallet_images" placeholder="@lang('Wallet Images')" multiple>
                                    </div>
                                    <small class="text--muted">@lang('Upload multiple images for the wallet header.')</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label> @lang('Wallet Image Effect')</label>
                                    <select class="form-control" name="wallet_image_effect">
                                        <option value="" {{ $general->wallet_image_effect == '' ? 'selected' : '' }}>@lang('None')</option>
                                        <option value="slide" {{ $general->wallet_image_effect == 'slide' ? 'selected' : '' }}>@lang('Slide')</option>
                                        <option value="fade" {{ $general->wallet_image_effect == 'fade' ? 'selected' : '' }}>@lang('Fade')</option>
                                        <option value="parallax" {{ $general->wallet_image_effect == 'parallax' ? 'selected' : '' }}>@lang('Parallax')</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>@lang('Header Background Slideshow')</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="wallet_header_slideshow" id="wallet_header_slideshow" {{ $general->wallet_header_slideshow ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="wallet_header_slideshow">@lang('Enable auto background slideshow')</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($general->wallet_images)
                            <div class="row mb-3">
                                <div class="col-12">
                                    <label>@lang('Existing Wallet Header Images')</label>
                                </div>
                                @foreach($general->wallet_images as $img)
                                    <div class="col-md-2 mb-2 text-center">
                                        @php $thumb = getFilePath('walletHeader') . '/thumb_' . $img; @endphp
                                        <img src="{{ file_exists($thumb) ? asset($thumb) : getImage(getFilePath('walletHeader') . '/' . $img) }}" class="img-thumbnail" style="height:80px;">
                                        <div class="form-check mt-1">
                                          <input class="form-check-input" type="checkbox" name="remove_wallet_images[]" value="{{ $img }}" id="rm_{{ $loop->index }}">
                                          <label class="form-check-label small" for="rm_{{ $loop->index }}">@lang('Remove')</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group ">
                                    <label> @lang('Site Title')</label>
                                    <input class="form-control" type="text" name="site_name" required value="{{$general->site_name}}">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Currency')</label>
                                    <input class="form-control" type="text" name="cur_text" required value="{{$general->cur_text}}">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group ">
                                    <label>@lang('Currency Symbol')</label>
                                    <input class="form-control" type="text" name="cur_sym" required value="{{$general->cur_sym}}">
                                </div>
                            </div>
                            <div class="form-group col-md-3 col-sm-6">
                                <label> @lang('Timezone')</label>
                                <select class="select2-basic" name="timezone">
                                    @foreach($timezones as $timezone)
                                    <option value="'{{ @$timezone}}'">{{ __($timezone) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-3 col-sm-6">
                                <label> @lang('Site Base Color')</label>
                                <div class="input-group">
                                    <span class="input-group-text p-0 border-0">
                                        <input type='text' class="form-control colorPicker" value="{{$general->base_color}}"/>
                                    </span>
                                    <input type="text" class="form-control colorCode" name="base_color" value="{{ $general->base_color }}"/>
                                </div>
                            </div>
                            <div class="form-group col-md-3 col-sm-6">
                                <label> @lang('Site Secondary Color')</label>
                                <div class="input-group">
                                    <span class="input-group-text p-0 border-0">
                                        <input type='text' class="form-control colorPicker" value="{{$general->secondary_color}}"/>
                                    </span>
                                    <input type="text" class="form-control colorCode" name="secondary_color" value="{{ $general->secondary_color }}"/>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label> @lang('Registration Bonus')</label>
                                    <div class="input-group">
                                        <input class="form-control" type="number" step="any" name="registration_bonus" required value="{{getAmount($general->registration_bonus)}}">
                                        <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label> @lang('Default Plan')</label>
                                    <select class="form-control" name="default_plan">
                                        <option value="">@lang('Select One')</option>
                                        <option value="0">@lang('None')</option>
                                        @foreach($plans as $plan)
                                            <option value="{{ $plan->id }}">{{ __($plan->name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>@lang('Country Restriction')</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="country_restriction" id="country_restriction" {{ $general->country_restriction ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="country_restriction">@lang('Enable')</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>@lang('Allowed Countries')</label>
                                    <select class="select2-basic" name="allowed_countries[]" multiple>
                                        @foreach ($countries as $key => $country)
                                            <option value="{{ $key }}" @if(is_array($general->allowed_countries) && in_array($key, $general->allowed_countries)) selected @endif>{{ __($country->country) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>@lang('Forced Country')</label>
                                    <select class="select2-basic" name="forced_country_code">
                                        <option value="">@lang('None')</option>
                                        @foreach ($countries as $key => $country)
                                            <option value="{{ $key }}" {{ $general->forced_country_code == $key ? 'selected' : '' }}>{{ __($country->country) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Premium Referral & Withdrawal Settings --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3 border-bottom pb-2"><i class="las la-crown text-warning"></i> @lang('Premium Referral & Withdrawal Settings')</h5>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>@lang('Premium Referral Base Value')</label>
                                    <div class="input-group">
                                        <input class="form-control" type="number" step="any" name="referral_premium_base_value" value="{{ getAmount($general->referral_premium_base_value ?? 100) }}">
                                        <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                    </div>
                                    <small class="text-muted">@lang('Commission when non-premium referrer\'s user buys premium plan')</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>@lang('Non-Premium Withdrawal Limit')</label>
                                    <div class="input-group">
                                        <input class="form-control" type="number" step="any" name="non_premium_withdraw_limit" value="{{ getAmount($general->non_premium_withdraw_limit ?? 1000) }}">
                                        <span class="input-group-text">{{ __($general->cur_text) }}</span>
                                    </div>
                                    <small class="text-muted">@lang('Lifetime withdrawal limit for non-premium users')</small>
                                </div>
                            </div>
                        </div>

                        {{-- PTC Settings --}}
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3 border-bottom pb-2"><i class="las la-mouse-pointer text-info"></i> @lang('PTC Settings')</h5>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>@lang('PTC Global Enable')</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="ptc_enable_global" id="ptc_enable_global" {{ ($general->ptc_enable_global ?? 1) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="ptc_enable_global">@lang('Enable PTC system globally')</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label>@lang('PTC Max Unlock Level')</label>
                                    <input class="form-control" type="number" name="ptc_max_unlock_level" value="{{ $general->ptc_max_unlock_level ?? 3 }}" min="1" max="10">
                                    <small class="text-muted">@lang('Maximum PTC unlock level for users')</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script-lib')
    <script src="{{ asset('assets/admin/js/spectrum.js') }}"></script>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/spectrum.css') }}">
@endpush

@push('script')
    <script>
        (function ($) {
            "use strict";
            $('.colorPicker').spectrum({
                color: $(this).data('color'),
                change: function (color) {
                    $(this).parent().siblings('.colorCode').val(color.toHexString().replace(/^#?/, ''));
                }
            });

            $('.colorCode').on('input', function () {
                var clr = $(this).val();
                $(this).parents('.input-group').find('.colorPicker').spectrum({
                    color: clr,
                });
            });

            $('select[name=timezone]').val("'{{ config('app.timezone') }}'").select2();
            $('.select2-basic').select2({
                dropdownParent:$('.card-body')
            });

            $('[name=default_plan]').val('{{ $general->default_plan }}');

        })(jQuery);

    </script>
    <script>
        // Admin live preview for wallet header images and settings
        (function(){
            function renderPreviewFiles(files, previewContainer){
                previewContainer.innerHTML = '';
                Array.from(files).forEach(function(f){
                    const reader = new FileReader();
                    const wrap = document.createElement('div'); wrap.className = 'd-inline-block me-2 mb-2';
                    const img = document.createElement('img'); img.style.height = '80px'; img.className = 'img-thumbnail';
                    reader.onload = function(e){ img.src = e.target.result; }
                    reader.readAsDataURL(f);
                    wrap.appendChild(img);
                    previewContainer.appendChild(wrap);
                });
            }

            document.addEventListener('DOMContentLoaded', function(){
                const input = document.querySelector('input[name="wallet_images[]"]');
                if(!input) return;
                let previewArea = document.getElementById('wallet_images_preview');
                if(!previewArea){
                    previewArea = document.createElement('div');
                    previewArea.id = 'wallet_images_preview';
                    input.parentNode.appendChild(previewArea);
                }
                input.addEventListener('change', function(e){
                    renderPreviewFiles(e.target.files, previewArea);
                });

                // live toggle preview for slideshow
                const toggle = document.querySelector('input[name="wallet_header_slideshow"]');
                const previewCard = document.querySelector('.wallet-header-card');
                if(toggle && previewCard){
                    toggle.addEventListener('change', function(){
                        if(this.checked){ previewCard.classList.remove('slideshow-disabled'); }
                        else { previewCard.classList.add('slideshow-disabled'); }
                    });
                }
            });
        })();
    </script>
@endpush

