@extends($activeTemplate . 'layouts.master')
@section('content')
 @include($activeTemplate.'layouts.breadcrumb')

<div class="row justify-content-center">
        <div class="col-md-12">
            <!-- <div class="show-filter mb-3 text-end">
                <button class="btn btn--base showFilterBtn btn-sm" type="button"><i class="las la-filter"></i> @lang('Filter')</button>
            </div> -->
          
            <div class="card custom--card  b-radius--10 mb-4">
                <div class="card-body">
                     
                    <button class="btn btns" data-bs-toggle="modal" data-bs-target="#generatePin"><i class="fa fa-fw fa-paper-plane"></i> @lang('Create Pin')</button>
                    
                    <span class="btn btn-base float-right">Total Pins created by you <span class="badge badge--success"> {{ $Totalpins }}</span></span>
                    
                </div>
            </div>

            <div class="card custom--card responsive-filter-card b-radius--10 mb-4">
                <div class="card-body">
                    <form>
                        <div class="d-flex flex-wrap gap-4">
                            <div class="flex-grow-1">
                                <label class="form--label form-label">@lang('E-Pin')</label>
                                <input class="form-control form--control" name="search" type="search" value="{{ request()->search }}">
                            </div>
                            <div class="flex-grow-1 select2-parent">
                                <label class="form--label form-label d-block">@lang('Type')</label>
                                <select class="form-control form--control select2" name="status" data-minimum-results-for-search="-1">
                                    <option value="">@lang('All')</option>
                                    <option value="0" @selected(request()->status == '0')>@lang('Unused')</option>
                                    <option value="1" @selected(request()->status == '1')>@lang('Used')</option>
                                </select>
                            </div>
                            
                            <div class="flex-grow-1 align-self-end">
                                <button class="btn btn--base w-100 h-50"><i class="las la-filter"></i> @lang('Filter')</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            
            <div class="card custom--card p-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="custom--table table">
                            <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Pin')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Details')</th>
                                    <th>@lang('Date')</th>
                                </tr>
                            </thead>
                            <tbody>
                                 @forelse ($pins as $pin)
                                    <tr>
                                        <td>
                                            @if ($pin->user_id)
                                                <span>{{ __($pin->user->username) }}</span>
                                            @else
                                                <span>@lang('N/A')</span>
                                            @endif
                                        </td>
                                        <td>{{ getAmount($pin->amount) }}
                                            {{ __(gs('cur_text')) }}</td>
                                        

                                        <td><input class="form-control" id="ref_{{ $loop->iteration }}" type="url"
                                            value="{{  $pin->pin }}" readonly><br/>

                                            <button class="btn btn--sm btn-success btn-block active" id="copybtn" type="button" onclick="myFunction('ref_{{ $loop->iteration }}')"> <span><i
                                                    class="fa fa-copy"></i> @lang('Copy')</span></button></td>
                                        

                                        <td>
                                            @if ($pin->status == 1)
                                                <span class="badge badge--success">@lang('Used')</span>
                                                <br>
                                                {{ diffforhumans($pin->updated_at) }}
                                            @elseif($pin->status == 0)
                                                <span class="badge badge--danger">@lang('Unused')</span>
                                            @endif
                                        </td>
                                        <td>{{ __($pin->details) }}</td>
                                        <td>
                                            {{ showDateTime($pin->created_at) }}
                                            <br>
                                            {{ diffforhumans($pin->created_at) }}
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
        @if ($pins->hasPages())
            <div class="mt-4">
                {{ paginateLinks($pins) }}
            </div>
        @endif
    </div>
@endsection
@push('modal')
<div class="modal fade" id="generatePin" tabindex="-1" aria-labelledby="exampleModalCenteredScrollableTitle" aria-hidden="true">
  
  <div class="modal-dialog" role="document">
    <div class="modal-content rounded-4 shadow">
        <div class="modal-header border-0 text-center">
            <div class="w-100">
                <img src="{{ asset($activeTemplateTrue . 'images/logo/Wordmark.png') }}" style="width:60px !important">                   
            </div>

        </div>
      <div class="modal-body p-4">
        <h3 class="fw-bold mb-0">@lang('Created Pin')</h3>
             <form action="{{ route('user.pin.generate') }}" method="post">
                @csrf

                <ul class="d-grid gap-4 my-5 list-unstyled">
                    <ul class="list-group mb-3">
                 
                          <li class="list-group-item d-flex justify-content-between lh-sm">
                            <input class="form-control" name="amount" type="number" value="9000" aria-describedby="basic-addon2" placeholder="@lang('Enter Amount')" step="any" readonly>
                                    <div class="input-group-text">
                                        {{ __(gs('cur_text')) }}
                                    </div>

                          </li>

                    </ul>
                        
                    <div class="modal-footer">
                        <button class="btn btn--danger" data-bs-dismiss="modal" type="button">@lang('Close')</button>
                        <button class="btn btn--primary bg--base" type="submit">@lang('Submit')</button>
                    </div>
                    
                </ul>
            </form>

      </div>
    </div>
  </div>
</div>

@endpush

@push('script')
    <script>
        "use strict";
        function myFunction(id) {
                var copyText = document.getElementById(id);
                copyText.select();
                copyText.setSelectionRange(0, 99999)
                document.execCommand("copy");
                notify('success', 'Pin copied ' + copyText.value);
            }
        (function($) {
            "use strict";
            

            $('.showFilterBtn').on('click', function() {
                $('.responsive-filter-card').slideToggle();
            });

        })(jQuery);
    </script>

@push('style')

<style>
    .table thead tr th {
        background-color: rgb(12 114 34 / 98%);
    }

    .btns {
        background-color: rgb(12 114 34 / 98%);
        color: white;}
</style>

@endpush
