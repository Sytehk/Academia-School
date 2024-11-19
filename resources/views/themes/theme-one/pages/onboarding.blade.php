@extends('themes.theme-one.layouts.app')

@section('content')
    <section class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-100 py-12">

        <div class="max-w-5xl mx-auto px-4">


            {{-- HEADER --}}
            <div class="text-center mb-12">

                <div
                    class="inline-flex items-center justify-center w-20 h-20 rounded-full
                bg-gradient-to-r from-purple-600 to-pink-500 shadow-xl mb-5">

                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2" />

                    </svg>

                </div>


                <h1 class="text-5xl font-black text-slate-800">
                    Create Your Institute
                </h1>

                <p class="text-slate-500 mt-3">
                    Complete your onboarding process and start managing your institute
                </p>

            </div>



            {{-- ALERT --}}

            @if (session('success'))
                <div class="mb-6 p-4 rounded-2xl bg-green-100 border border-green-300 text-green-700">
                    {{ session('success') }}
                </div>
            @endif


            @if (session('error'))
                <div class="mb-6 p-4 rounded-2xl bg-red-100 border border-red-300 text-red-700">
                    {{ session('error') }}
                </div>
            @endif



            <form action="{{ route('onboarding.submit') }}" method="POST" enctype="multipart/form-data"
                class="
bg-white/80
backdrop-blur-xl
border border-white
shadow-2xl
rounded-[35px]
p-6 md:p-12
space-y-12
">


                @csrf



                {{-- ================= INSTITUTE ================= --}}

                <div>

                    <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-3">

                        <span class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600">
                            🏫
                        </span>

                        Institute Information

                    </h2>



                    <div class="grid md:grid-cols-2 gap-6">



                        @foreach ([['institute_name', 'Institute Name', 'text'], ['institute_email', 'Institute Email', 'email'], ['institute_phone', 'Institute Phone', 'text'], ['institute_domain', 'Domain Name', 'text'], ['institute_type', 'Institute Type', 'text']] as $field)
                            <div class="relative">


                                <input type="{{ $field[2] }}" name="{{ $field[0] }}" value="{{ old($field[0]) }}"
                                    class="
peer
w-full
rounded-2xl
border
border-slate-300
px-5
pt-6
pb-3
focus:border-purple-500
focus:ring-2
focus:ring-purple-200
outline-none
"
                                    placeholder=" ">



                                <label class="
absolute
left-5
top-2
text-xs
text-slate-500
peer-focus:text-purple-600
">

                                    {{ $field[1] }}

                                </label>


                            </div>
                        @endforeach



                    </div>



                    {{-- ADDRESS --}}

                    <div class="relative mt-6">

                        <textarea name="institute_address" rows="3"
                            class="
peer
w-full
rounded-2xl
border
px-5
pt-6
pb-3
outline-none
focus:ring-2
focus:ring-purple-200
"
                            placeholder=" ">{{ old('institute_address') }}</textarea>


                        <label class="absolute left-5 top-2 text-xs text-slate-500">
                            Institute Address
                        </label>


                    </div>



                    {{-- LOGO --}}

                    <div class="mt-6">

                        <label class="font-semibold text-slate-600">
                            Institute Logo
                        </label>


                        <input type="file" name="institute_logo" onchange="previewImage(event,'logoPreview')"
                            class="mt-3 w-full border rounded-xl p-3">


                        <img id="logoPreview" class="hidden mt-4 w-32 h-32 rounded-2xl object-cover shadow">


                    </div>



                </div>





                {{-- ================= USER ================= --}}


                <div>


                    <h2 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-3">

                        <span class="w-10 h-10 rounded-xl bg-pink-100 flex items-center justify-center text-pink-600">
                            👤
                        </span>

                        Admin Account

                    </h2>




                    <div class="grid md:grid-cols-2 gap-6">



                        @foreach ([['user_name', 'Admin Name', 'text'], ['user_email', 'Admin Email', 'email'], ['user_phone', 'Admin Phone', 'text'], ['password', 'Password', 'password'], ['password_confirmation', 'Confirm Password', 'password']] as $field)
                            <div class="relative">


                                <input type="{{ $field[2] }}" name="{{ $field[0] }}"
                                    class="
peer
w-full
rounded-2xl
border
px-5
pt-6
pb-3
outline-none
focus:ring-2
focus:ring-purple-200
"
                                    placeholder=" ">



                                <label class="
absolute
left-5
top-2
text-xs
text-slate-500
">

                                    {{ $field[1] }}

                                </label>


                            </div>
                        @endforeach



                    </div>



                    {{-- AVATAR --}}

                    <div class="mt-6">


                        <label class="font-semibold text-slate-600">
                            Admin Avatar
                        </label>


                        <input type="file" name="user_avatar" onchange="previewImage(event,'avatarPreview')"
                            class="mt-3 w-full border rounded-xl p-3">


                        <img id="avatarPreview" class="hidden mt-4 w-32 h-32 rounded-full object-cover shadow">


                    </div>



                </div>




                {{-- BUTTON --}}

                <div class="text-center pt-5">


                    <button
                        class="
px-12
py-4
rounded-full
bg-gradient-to-r
from-purple-600
to-pink-500
text-white
font-bold
shadow-xl
hover:scale-105
transition
">

                        Create Institute 🚀

                    </button>


                </div>



            </form>


        </div>

    </section>




    <script>
        function previewImage(event, id)

        {

            let file = event.target.files[0];

            let preview = document.getElementById(id);


            if (file)

            {

                preview.src = URL.createObjectURL(file);

                preview.classList.remove('hidden');

            }


        }
    </script>
@endsection
