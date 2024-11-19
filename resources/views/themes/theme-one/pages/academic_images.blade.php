@extends('themes.theme-one.layouts.app')

@section('content')
    <section class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-slate-100 py-14">

        <div class="max-w-6xl mx-auto px-4">

            {{-- HEADER --}}
            <div class="text-center mb-10">
                <h1 class="text-4xl font-black text-slate-800">Academic Gallery</h1>
                <p class="text-slate-500 mt-2">Explore school activities, events & memories</p>
            </div>

            {{-- GALLERY GRID --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">

                @foreach ($academicImages as $item)
                    <div class="group cursor-pointer relative overflow-hidden rounded-2xl shadow-md bg-white"
                        onclick="openModal('{{ asset('storage/academic_images/' . $item->image) }}', '{{ $item->title }}')">

                        <img src="{{ asset('storage/academic_images/' . $item->image) }}"
                            class="w-full h-48 object-cover transform group-hover:scale-110 transition duration-300">

                        {{-- OVERLAY --}}
                        <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition"></div>

                        {{-- TEXT --}}
                        <div class="absolute bottom-0 p-3 text-white">
                            <h3 class="font-bold text-sm">{{ $item->title }}</h3>
                            <p class="text-xs opacity-80">{{ \Illuminate\Support\Str::limit($item->heading, 40) }}</p>
                        </div>

                    </div>
                @endforeach

            </div>
        </div>
    </section>

    {{-- LIGHTBOX MODAL --}}
    <div id="imageModal" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50">

        <div class="relative max-w-4xl w-full px-4">

            <button onclick="closeModal()" class="absolute top-2 right-2 bg-white text-black px-3 py-1 rounded-full">
                ✖
            </button>

            <img id="modalImage" class="w-full max-h-[80vh] object-contain rounded-xl shadow-xl">

            <div class="text-center text-white mt-3 font-semibold" id="modalTitle"></div>

        </div>
    </div>

    {{-- SCRIPT --}}
    <script>
        function openModal(image, title) {
            document.getElementById('imageModal').classList.remove('hidden');
            document.getElementById('imageModal').classList.add('flex');

            document.getElementById('modalImage').src = image;
            document.getElementById('modalTitle').innerText = title;
        }

        function closeModal() {
            document.getElementById('imageModal').classList.add('hidden');
            document.getElementById('imageModal').classList.remove('flex');
        }

        // close on outside click
        document.getElementById('imageModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
@endsection
