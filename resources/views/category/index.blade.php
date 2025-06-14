<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Category') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- dipisahkan tombol add nya -->
                    <div class="mb-4">
                        <a href="{{ route('category.create') }}"
                            class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-semibold hover:bg-blue-600 transition">
                            Add
                        </a>
                    </div>


                    <div class="overflow-x-auto">
                        <table id="categoryTable" class="w-full table-auto text-sm border border-gray-300 rounded-lg">
                            <thead class="bg-gray-200 text-gray-700">
                                <tr>
                                    <th class="px-4 py-2 border">Name</th>
                                    <th class="px-4 py-2 border">Type</th>
                                    <th class="px-4 py-2 border">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($categories as $category)
                                <tr class="hover:bg-gray-100">
                                    <td class="px-4 py-2 border">{{ $category->name }}</td>
                                    <td class="px-4 py-2 border text-center">
                                        <span class="px-3 py-1 text-white text-xs rounded-full {{ $category->type == \App\Enums\CategoryType::INCOME->value ? 'bg-green-500' : 'bg-red-500' }}">
                                            {{ $category->type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 border text-center space-x-2">
                                        <a href="{{ route('category.edit', $category->name) }}"
                                            class="inline-block px-4 py-1 bg-yellow-400 text-white text-sm font-semibold rounded-full hover:bg-yellow-500">
                                            Edit
                                        </a>
                                        <button onclick="handleDelete('{{ $category->name }}')"
                                            class="inline-block px-4 py-1 bg-red-500 text-white text-sm font-semibold rounded-full hover:bg-red-600">
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

    <!-- panggil CSS datatables -->
    @push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    @endpush

    @push('js')
    <!-- panggil js datatables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <!-- edit tombol dan isi navigasi datatables -->
    <script>
        $(document).ready(function() {
            $('#categoryTable').DataTable({
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

        function handleDelete(name) {
            Swal.fire({
                title: "Are you sure?",
                text: "You will not be able to revert this!",
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
                        url: "{{ route('category.destroy', ':name') }}".replace(':name', name),
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            $.ajax({
                                url: "{{ route('category.index') }}",
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
