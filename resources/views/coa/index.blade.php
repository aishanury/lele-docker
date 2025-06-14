<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Chart Of Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                <div class="p-6 text-gray-900">
                    <div class="mb-4">
                        <a href="{{ route('coa.create') }}"
                            class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-semibold hover:bg-blue-600 transition">
                            Add
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table id="coaTable" class="w-full table-auto text-sm border border-gray-300 rounded-lg">
                            <thead class="bg-gray-200 text-gray-700">
                                <tr>
                                    <th class="px-4 py-2 border">Code</th>
                                    <th class="px-4 py-2 border">Name</th>
                                    <th class="px-4 py-2 border">Category</th>
                                    <th class="px-4 py-2 border">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($chartOfAccounts as $coa)
                                <tr class="hover:bg-gray-100">
                                    <td class="px-4 py-2 border">{{ $coa->code }}</td>
                                    <td class="px-4 py-2 border">{{ $coa->name }}</td>
                                    <td class="px-4 py-2 border text-center">{{ $coa->category->name }} <span class="px-2 py-1 text-white {{ $coa->category->type == \App\Enums\CategoryType::INCOME->value ? 'bg-green-500 rounded-full' : 'bg-red-500 rounded-full' }} text-xs">{{ $coa->category->type }}</span></td>
                                    <td class="px-4 py-2 border text-center">
                                        <a href="{{ route('coa.edit', $coa->code) }}" class="px-8 py-2 bg-yellow-400 text-center text-sm font-semibold rounded-full">Edit</a>
                                        <button
                                            class="px-8 py-2 bg-red-400 text-center text-sm font-semibold rounded-full"
                                            onclick="handleDelete('{{ $coa->code }}')">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @endpush

    @push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#coaTable').DataTable({
                responsive: true,
                pagingType: "full_numbers",
                lengthMenu: [5, 10, 25, 50, 100],
                language: {
                    paginate: {
                        previous: "‹",
                        next: "›",
                        first: "«",
                        last: "»"
                    }
                },
                drawCallback: function() {
                    $('.dataTables_paginate').addClass('flex justify-center mt-4');
                }
            });
        });

        function handleDelete(code) {
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    showLoading();
                    $.ajax({
                        type: "DELETE",
                        url: "{{ route('coa.destroy', ':code') }}".replace(':code', code),
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $.ajax({
                                url: "{{ route('coa.index') }}",
                                type: "GET",
                                success: function(data) {
                                    hideLoading();
                                    $("tbody").html($(data).find("tbody")
                                        .html());
                                    Swal.fire({
                                        title: 'Success!',
                                        text: 'Your data has been deleted successfully!',
                                        icon: 'success'
                                    })
                                },
                                error: function() {
                                    hideLoading();
                                    Swal.fire({
                                        title: 'Error!',
                                        text: 'Something went errors, please try again later!',
                                        icon: 'error'
                                    })
                                }
                            });
                        },
                        error: function(xhr) {
                            hideLoading();
                            if (xhr.status == 409) {
                                Swal.fire({
                                    title: 'Error!',
                                    text: xhr.responseJSON.message,
                                    icon: 'error'
                                })
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Something went errors, please try again later!',
                                    icon: 'error'
                                })
                            }
                        }
                    });
                }
            });
        }
    </script>
    @endpush

</x-app-layout>
