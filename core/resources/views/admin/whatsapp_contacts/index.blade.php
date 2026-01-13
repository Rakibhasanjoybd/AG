@extends('admin.layouts.app')

@section('panel')
<div class="row">
    <div class="col-lg-12">
        <div class="card b-radius--10">
            <div class="card-body p-0">
                <div class="table-responsive--md table-responsive">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="card-title mb-0">{{ $pageTitle }}</h5>
                        <a href="{{ route('admin.whatsapp.contacts.create') }}" class="btn btn-sm btn-primary">
                            <i class="las la-plus"></i> Add New Contact
                        </a>
                    </div>
                    
                    @if($contacts->count() > 0)
                    <table class="table table--light style--two">
                        <thead>
                            <tr>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>Department</th>
                                <th>Phone Number</th>
                                <th>Display Order</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contacts as $contact)
                            <tr>
                                <td>
                                    <img src="{{ $contact->profile_image_url }}" 
                                         alt="{{ $contact->name }}" 
                                         class="rounded-circle" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </td>
                                <td>{{ $contact->name }}</td>
                                <td>
                                    <span class="badge badge--info">{{ $contact->department }}</span>
                                </td>
                                <td>{{ $contact->phone_number }}</td>
                                <td>
                                    <span class="badge badge--dark">{{ $contact->display_order }}</span>
                                </td>
                                <td>
                                    @if($contact->is_active)
                                        <span class="badge badge--success">Active</span>
                                    @else
                                        <span class="badge badge--warning">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="button--group">
                                        <a href="{{ route('admin.whatsapp.contacts.edit', $contact->id) }}" 
                                           class="btn btn-sm btn-primary" 
                                           data-toggle="tooltip" 
                                           title="Edit">
                                            <i class="las la-pen"></i>
                                        </a>
                                        
                                        <a href="{{ route('admin.whatsapp.contacts.toggle', $contact->id) }}" 
                                           class="btn btn-sm btn-{{ $contact->is_active ? 'warning' : 'success' }}" 
                                           data-toggle="tooltip" 
                                           title="{{ $contact->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="las la-{{ $contact->is_active ? 'ban' : 'check' }}"></i>
                                        </a>
                                        
                                        <button type="button" 
                                                class="btn btn-sm btn-danger confirmationBtn" 
                                                data-question="Are you sure you want to delete this contact?"
                                                data-action="{{ route('admin.whatsapp.contacts.destroy', $contact->id) }}"
                                                data-toggle="tooltip" 
                                                title="Delete">
                                            <i class="las la-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center p-5">
                        <i class="las la-frown" style="font-size: 72px; color: #ccc;"></i>
                        <p class="mt-3">No WhatsApp contacts found. <a href="{{ route('admin.whatsapp.contacts.create') }}">Add your first contact</a></p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div id="confirmationModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmation</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="las la-times"></i>
                </button>
            </div>
            <form action="" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <p class="question"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">No</button>
                    <button type="submit" class="btn btn-primary">Yes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    (function($){
        "use strict";
        
        $('.confirmationBtn').on('click', function () {
            var modal = $('#confirmationModal');
            var question = $(this).data('question');
            var action = $(this).data('action');
            
            modal.find('.question').text(question);
            modal.find('form').attr('action', action);
            modal.modal('show');
        });
    })(jQuery);
</script>
@endpush
