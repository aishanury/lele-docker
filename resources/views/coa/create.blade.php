<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create Chart Of Account') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <a href="{{ route('coa.index') }}"
                        class="px-8 py-2 bg-blue-300 text-center text-sm font-semibold mb-2">Back</a>

                    <div class="flex gap-2 mt-4 w-full">
                        <div class="w-1/3">
                            <x-input-label>Code</x-input-label>
                            <x-text-input class="w-full" id="code"></x-text-input>
                        </div>

                        <div class="w-1/3">
                            <x-input-label>Name</x-input-label>
                            <x-text-input class="w-full" id="name"></x-text-input>
                        </div>

                        <div class="w-1/3">
                            <x-input-label>Category Name</x-input-label>
                            <x-select-input class="w-full" id="categoryName" :options="$categoryOptions" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-3">
                        <button class="px-8 py-2 bg-yellow-400 text-center text-sm font-semibold"
                            id="addBtn">Add</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            $(document).ready(function() {

                $("#addBtn").on("click", function() {
                    const code = $("#code").val();
                    const name = $("#name").val();
                    const categoryName = $("#categoryName").val();

                    showLoading();
                    $.ajax({
                        type: "POST",
                        url: "{{ route('coa.store') }}",
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            name: name,
                            code: code,
                            category_name: categoryName
                        },
                        success: function(response) {
                            hideLoading();
                            Swal.fire({
                                title: 'Success!',
                                text: 'Your data has been added successfully!',
                                icon: 'success'
                            })
                            $("#code").val("");
                            $("#name").val("");
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;
                                let errorMessages = "";

                                $.each(errors, function(key, value) {
                                    errorMessages += value[0] +
                                        "\n";
                                });

                                hideLoading();
                                Swal.fire({
                                    title: 'Validation Errors!',
                                    text: errorMessages,
                                    icon: 'error'
                                })
                            } else {
                                hideLoading();
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Something went errors, please try again later!',
                                    icon: 'error'
                                })
                            }
                        }
                    });
                });

            });
        </script>
    @endpush
</x-app-layout>
