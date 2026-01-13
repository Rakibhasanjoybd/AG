@extends('admin.layouts.app')
@section('panel')
<div class="row mb-none-30">
  <div class="col-md-12">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive--sm">
                <table class="table table--light style--two">
                    <thead>
                        <tr>
                            <th scope="col">@lang('Name')</th>
                            <th scope="col">@lang('Price')</th>
                            <th scope="col">@lang('Limit/Day')</th>
                            <th scope="col">@lang('Per Ad Earn')</th>
                            <th scope="col">@lang('Validity')</th>
                            <th scope="col">@lang('Withdraw Limit')</th>
                            <th scope="col">@lang('Referral')</th>
                            <th scope="col">@lang('Type')</th>
                            <th scope="col">@lang('Status')</th>
                            <th scope="col">@lang('Action')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                        <tr>
                            <td data-label="@lang('Name')">{{$plan->name}}</td>
                            <td data-label="@lang('Price')" class="font-weight-bold">{{ showAmount($plan->price) }} {{$general->cur_text}}</td>
                            <td data-label="@lang('Limit/Day')">{{ $plan->daily_limit}} @lang('PTC')</td>
                            <td data-label="@lang('Per Ad Earn')">{{ showAmount($plan->ptc_view_amount ?? 0) }} {{$general->cur_text}}</td>
                            <td data-label="@lang('Validity')">{{ $plan->validity}} @lang('Day')</td>
                            <td data-label="@lang('Withdraw Limit')">
                                <span class="badge badge--primary">{{ $plan->anytime_withdraw_limit ?? 5 }} @lang('Anytime')</span>
                                @if($plan->weekly_withdraw_enabled)
                                <span class="badge badge--info">@lang('Weekly')</span>
                                @endif
                            </td>
                            <td data-label="@lang('Referral')">@lang('Level') {{ $plan->ref_level }}</td>
                            <td data-label="@lang('Type')">
                                @if($plan->is_premium_package)
                                    <span class="badge badge--warning"><i class="las la-crown"></i> @lang('Premium') #{{ $plan->package_number }}</span>
                                @else
                                    <span class="badge badge--secondary">@lang('Free')</span>
                                @endif
                            </td>
                            <td data-label="@lang('Status')">
                                @if($plan->status == 1)
                                    <span class="badge badge--success">@lang('Active')</span>
                                @else
                                    <span class="badge badge--danger">@lang('Inactive')</span>
                                @endif
                            </td>
                            <td data-label="@lang('Action')">
                                <button class="btn btn-outline--primary btn-sm planBtn"
                                    data-id="{{ $plan->id }}"
                                    data-name="{{ $plan->name }}"
                                    data-price="{{ getAmount($plan->price) }}"
                                    data-daily_limit="{{ $plan->daily_limit }}"
                                    data-ptc_view_amount="{{ getAmount($plan->ptc_view_amount ?? 0) }}"
                                    data-validity="{{ $plan->validity }}"
                                    data-status="{{ $plan->status }}"
                                    data-ref_level="{{ $plan->ref_level}}"
                                    data-anytime_withdraw_limit="{{ $plan->anytime_withdraw_limit ?? 5 }}"
                                    data-weekly_withdraw_day="{{ $plan->weekly_withdraw_day ?? 0 }}"
                                    data-weekly_withdraw_enabled="{{ $plan->weekly_withdraw_enabled ?? 1 }}"
                                    data-package_number="{{ $plan->package_number ?? 0 }}"
                                    data-is_premium_package="{{ $plan->is_premium_package ?? 0 }}"
                                    data-image="{{ $plan->image ? asset('assets/images/plan/'.$plan->image) : '' }}"
                                    data-act="Edit">
                                    <i class="la la-pencil"></i> @lang('Edit')
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td class="text-muted text-center" colspan="100%">{{ __($emptyMessage) }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="planModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title"><span class="act"></span> @lang('Subscription Plan')</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                <i class="las la-times"></i>
            </button>
            </div>
            <form action="{{ route('admin.plan.save') }}" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="id">
                <div class="modal-body">
                    <div class="row">
                        <!-- Plan Image Upload -->
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="image">@lang('Plan Image') <small class="text-muted">(GIF, PNG, JPG - Max 5MB)</small></label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="plan-image-preview" id="imagePreviewContainer" style="display:none;">
                                        <img id="imagePreview" src="" alt="Plan Image" style="max-width: 120px; max-height: 120px; border-radius: 8px; border: 2px solid #ddd;">
                                        <button type="button" class="btn btn-sm btn-danger mt-1" id="removeImageBtn" style="display:none;">
                                            <i class="las la-times"></i> @lang('Remove')
                                        </button>
                                    </div>
                                    <div class="flex-grow-1">
                                        <input type="file" class="form-control" name="image" id="planImageInput" accept=".jpg,.jpeg,.png,.gif">
                                        <small class="text-muted">@lang('Upload an image for this plan (supports animated GIF)')</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Basic Info -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">@lang('Name') </label>
                                <input type="text" class="form-control" name="name" placeholder="@lang('Plan Name')" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="price">@lang('Price') </label>
                                <div class="input-group">
                                    <input type="text" class="form-control has-append" name="price" placeholder="@lang('Price of Plan')" required>
                                    <div class="input-group-text">{{ $general->cur_text }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="daily_limit">@lang('Daily Ad Limit')</label>
                                <input type="number" class="form-control" name="daily_limit" placeholder="@lang('Daily Ad Limit')" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ptc_view_amount">@lang('Per Ad View Amount')</label>
                                <div class="input-group">
                                    <input type="number" step="any" class="form-control" name="ptc_view_amount" value="0" min="0" placeholder="@lang('Amount per ad view')" required>
                                    <div class="input-group-text">{{ $general->cur_text }}</div>
                                </div>
                                <small class="text-muted">@lang('Amount user earns for each PTC ad view')</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="validity">@lang('Validity')</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="validity" placeholder="@lang('Validity')" required>
                                    <div class="input-group-text">@lang('Days')</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="ref_level">@lang('Referral Commission') </label>
                                <select name="ref_level" class="form-control" required>
                                    <option value="0"> @lang('NO Referral Commission')</option>
                                    @for($i = 1; $i <= $levels; $i++)
                                    <option value="{{$i}}"> @lang('Up to') {{$i}}  @lang('Level')</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="package_number">@lang('Package Number')</label>
                                <input type="number" class="form-control" name="package_number" value="0" min="0" max="255" placeholder="0" required>
                                <small class="text-muted">@lang('Package tier/level (0 = free, 1+ = premium packages)')</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="is_premium_package">@lang('Premium Package')</label>
                                <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-warning" data-offstyle="-secondary" data-bs-toggle="toggle" data-on="@lang('Premium')" data-off="@lang('Free')" name="is_premium_package">
                                <small class="text-muted">@lang('Premium packages bypass 1000 BDT withdrawal limit')</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status">@lang('Status')</label>
                                <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-on="@lang('Enable')" data-off="@lang('Disable')" name="status">
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="mb-3"><i class="las la-money-bill-wave text-primary"></i> @lang('Withdrawal Settings')</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="anytime_withdraw_limit">@lang('Anytime Withdraw Limit')</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" name="anytime_withdraw_limit" value="5" min="0" max="100" placeholder="5" required>
                                    <div class="input-group-text">@lang('Times')</div>
                                </div>
                                <small class="text-muted">@lang('Number of anytime withdrawals after plan purchase')</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="weekly_withdraw_day">@lang('Weekly Withdraw Day')</label>
                                <select name="weekly_withdraw_day" class="form-control" required>
                                    <option value="0">@lang('Sunday')</option>
                                    <option value="1">@lang('Monday')</option>
                                    <option value="2">@lang('Tuesday')</option>
                                    <option value="3">@lang('Wednesday')</option>
                                    <option value="4">@lang('Thursday')</option>
                                    <option value="5">@lang('Friday')</option>
                                    <option value="6">@lang('Saturday')</option>
                                </select>
                                <small class="text-muted">@lang('Day for weekly withdrawal after anytime limit')</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="weekly_withdraw_enabled">@lang('Weekly Withdraw')</label>
                                <input type="checkbox" data-width="100%" data-height="50" data-onstyle="-primary" data-offstyle="-warning" data-bs-toggle="toggle" data-on="@lang('Enabled')" data-off="@lang('Disabled')" name="weekly_withdraw_enabled" checked>
                                <small class="text-muted">@lang('Allow weekly withdraw after anytime exhausted')</small>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-2">
                        <i class="las la-info-circle"></i>
                        <strong>@lang('How it works:')</strong>
                        <ul class="mb-0 mt-2">
                            <li>@lang('After plan purchase, user gets specified anytime withdrawals')</li>
                            <li>@lang('Once anytime withdrawals are used, user can only withdraw on the specified weekly day')</li>
                            <li>@lang('Each week, user gets 1 withdrawal on the specified day')</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn--primary w-100">@lang('Submit')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('breadcrumb-plugins')
    <button class="btn btn-outline--primary btn-sm planBtn" data-id="0" data-act="Add" data-bs-toggle="modal" data-bs-target="#planModal"><i class="las la-plus"></i> @lang('Add New')</button>
@endpush


@push('script')
<script>
    (function($){
        "use strict";

        // Image preview on file select
        $('#planImageInput').on('change', function() {
            var file = this.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result);
                    $('#imagePreviewContainer').show();
                    $('#removeImageBtn').show();
                }
                reader.readAsDataURL(file);
            }
        });

        // Remove image button
        $('#removeImageBtn').on('click', function() {
            $('#planImageInput').val('');
            $('#imagePreviewContainer').hide();
            $('#removeImageBtn').hide();
        });

        $('.planBtn').on('click', function() {
            var modal = $('#planModal');
            modal.find('.act').text($(this).data('act'));
            modal.find('input[name=id]').val($(this).data('id'));
            modal.find('input[name=name]').val($(this).data('name'));
            modal.find('input[name=price]').val($(this).data('price'));
            modal.find('input[name=daily_limit]').val($(this).data('daily_limit'));
            modal.find('input[name=ptc_view_amount]').val($(this).data('ptc_view_amount') || 0);
            modal.find('input[name=validity]').val($(this).data('validity'));
            modal.find('input[name=status]').bootstrapToggle($(this).data('status') == 1 ? 'on' : 'off');
            modal.find('select[name=ref_level]').val($(this).data('ref_level'));

            // Withdrawal settings
            modal.find('input[name=anytime_withdraw_limit]').val($(this).data('anytime_withdraw_limit') || 5);
            modal.find('select[name=weekly_withdraw_day]').val($(this).data('weekly_withdraw_day') || 0);
            modal.find('input[name=weekly_withdraw_enabled]').bootstrapToggle($(this).data('weekly_withdraw_enabled') == 1 ? 'on' : 'off');

            // Package settings
            modal.find('input[name=package_number]').val($(this).data('package_number') || 0);
            modal.find('input[name=is_premium_package]').bootstrapToggle($(this).data('is_premium_package') == 1 ? 'on' : 'off');

            // Image preview for existing plan
            var imageUrl = $(this).data('image');
            if (imageUrl && $(this).data('id') != 0) {
                $('#imagePreview').attr('src', imageUrl);
                $('#imagePreviewContainer').show();
                $('#removeImageBtn').hide();
            } else {
                $('#imagePreviewContainer').hide();
                $('#removeImageBtn').hide();
            }
            $('#planImageInput').val('');

            if($(this).data('id') == 0){
                modal.find('form')[0].reset();
                modal.find('input[name=status]').bootstrapToggle('on');
                modal.find('input[name=weekly_withdraw_enabled]').bootstrapToggle('on');
                modal.find('input[name=is_premium_package]').bootstrapToggle('off');
                modal.find('input[name=anytime_withdraw_limit]').val(5);
                modal.find('input[name=package_number]').val(0);
                modal.find('input[name=ptc_view_amount]').val(0);
                $('#imagePreviewContainer').hide();
            }
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush
