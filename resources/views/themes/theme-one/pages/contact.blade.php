@extends('themes.theme-one.layouts.app')

@section('content')
    <section class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-100 py-14">

        <div class="max-w-6xl mx-auto px-4">


            {{-- HEADER --}}
            <div class="text-center mb-12">

                <div
                    class="inline-flex items-center justify-center w-20 h-20 rounded-full
bg-gradient-to-r from-purple-600 to-pink-500 shadow-xl mb-5">

                    <span class="text-4xl">📩</span>

                </div>

                <h1 class="text-5xl font-black text-slate-800">
                    Contact Us
                </h1>

                <p class="text-slate-500 mt-3">
                    Have questions? Send us a message
                </p>

            </div>



            {{-- SESSION MESSAGE --}}

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




            <div class="grid lg:grid-cols-2 gap-10">



                {{-- FORM --}}

                <form action="{{ route('contact.submit') }}" method="POST"
                    class="
bg-white/80
backdrop-blur-xl
rounded-[35px]
shadow-2xl
border
border-white
p-8
space-y-6
">

                    @csrf


                    <h2 class="text-2xl font-bold text-slate-800">
                        Send Message
                    </h2>



                    @php

                        $inputClass = "
peer
w-full
rounded-2xl
border
px-5
pt-6
pb-3
outline-none
focus:ring-2
focus:ring-purple-300
";

                    @endphp




                    {{-- NAME --}}

                    <div class="relative">

                        <input name="name" value="{{ old('name') }}" required class="{{ $inputClass }}"
                            placeholder=" ">

                        <label class="absolute left-5 top-2 text-xs text-slate-500">
                            Your Name *
                        </label>


                        @error('name')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror

                    </div>





                    {{-- EMAIL --}}

                    <div class="relative">

                        <input type="email" name="email" value="{{ old('email') }}" required
                            class="{{ $inputClass }}" placeholder=" ">


                        <label class="absolute left-5 top-2 text-xs text-slate-500">
                            Email *
                        </label>


                        @error('email')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror


                    </div>






                    {{-- PHONE --}}

                    <div class="relative">


                        <input name="phone" value="{{ old('phone') }}" required class="{{ $inputClass }}"
                            placeholder=" ">


                        <label class="absolute left-5 top-2 text-xs text-slate-500">
                            Phone *
                        </label>


                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror


                    </div>






                    {{-- MESSAGE --}}

                    <div class="relative">


                        <textarea name="message" rows="5" required class="{{ $inputClass }}" placeholder=" ">{{ old('message') }}</textarea>


                        <label class="absolute left-5 top-2 text-xs text-slate-500">
                            Message *
                        </label>


                        @error('message')
                            <p class="text-red-500 text-sm mt-1">
                                {{ $message }}
                            </p>
                        @enderror


                    </div>





                    <button
                        class="
w-full
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

                        Send Message 🚀

                    </button>



                </form>







                {{-- RIGHT SIDE --}}

                <div class="space-y-6">


                    <div
                        class="
bg-white/80
backdrop-blur-xl
rounded-[35px]
shadow-2xl
overflow-hidden
border
border-white
">


                        <iframe src="{{ $settings['map_url'] ?? 'https://maps.google.com/maps?q=Dhaka&output=embed' }}"
                            class="w-full h-[350px]" style="border:0" loading="lazy">

                        </iframe>


                    </div>






                    {{-- INFO --}}

                    <div class="space-y-4">


                        <div class="
bg-white
rounded-3xl
p-5
shadow-lg
flex
items-center
gap-4
">


                            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center text-2xl">

                                📍

                            </div>


                            <div>

                                <h4 class="font-bold">
                                    Address
                                </h4>

                                <p class="text-slate-500 text-sm">

                                    {{ $settings['address'] ?? 'Your institute address' }}

                                </p>


                            </div>


                        </div>





                        <div class="
bg-white
rounded-3xl
p-5
shadow-lg
flex
items-center
gap-4
">


                            <div class="w-12 h-12 rounded-xl bg-pink-100 flex items-center justify-center text-2xl">

                                ☎️

                            </div>


                            <div>

                                <h4 class="font-bold">
                                    Phone
                                </h4>


                                <p class="text-slate-500 text-sm">

                                    {{ $settings['phone'] ?? '+880000000000' }}

                                </p>


                            </div>


                        </div>


                    </div>



                </div>


            </div>



        </div>


    </section>
@endsection
