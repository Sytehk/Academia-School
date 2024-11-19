<style>
    .hero-slider {
        height: 754px;
        overflow: hidden;
    }

    .hero-track {
        display: flex;
        height: 100%;
        transition: transform 1s ease-in-out;
    }

    .hero-slide {
        min-width: 100%;
        height: 100%;
        position: relative;
    }

    .hero-slide img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: .75;
        transition: 8s ease;
    }

    .hero-slide.active img {
        transform: scale(1.05);
        opacity: 1;
    }

    .hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg,
                rgba(2, 6, 23, .85),
                rgba(2, 6, 23, .45),
                transparent);
    }


    @media(max-width:1024px) {

        .hero-slider {
            height: 500px;
        }

    }


    @media(max-width:640px) {

        .hero-slider {
            height: 420px;
        }


        .hero-content h1 {
            font-size: 32px;
        }


        .hero-content p {
            font-size: 15px;
        }

    }
</style>

<section id="home">

    @if ($data['banners']->count())


        <div x-data="{
            active: 0,
            total: {{ $data['banners']->count() }},
            timer: null,
            pause: false,
        
            start() {
        
                this.timer = setInterval(() => {
        
                    if (!this.pause)
                        this.active = (this.active + 1) % this.total
        
                }, 5000)
        
            },
        
            next() {
        
                this.active = (this.active + 1) % this.total
        
            },
        
            prev() {
        
                this.active = (this.active - 1 + this.total) % this.total
        
            }
        
        }" x-init="start()" @mouseenter="pause=true" @mouseleave="pause=false"
            class="hero-slider relative bg-black">



            <div class="hero-track" :style="`transform:translateX(-${active*100}%)`">


                @foreach ($data['banners'] as $index => $banner)
                    <div class="hero-slide" :class="active == {{ $index }} ? 'active' : ''">


                        <img src="{{ asset('storage/banners/' . $banner->image) }}">


                        <div class="hero-overlay"></div>



                        <div class="absolute inset-0 flex items-center">


                            <div class="px-5 md:px-10 max-w-7xl mx-auto w-full">


                                <div class="hero-content text-white max-w-3xl">


                                    <h1 class="
text-4xl
md:text-6xl
font-bold
leading-tight
">

                                        {{ $banner->title }}

                                    </h1>


                                    @if ($banner->description)
                                        <p class="
mt-3
text-gray-200
md:text-lg
">

                                            {{ $banner->description }}

                                        </p>
                                    @endif



                                    @if ($banner->button_name && $banner->button_link)
                                        <a href="{{ $banner->button_link }}"
                                            class="
inline-block
mt-5
px-6
py-3
rounded-full
bg-white
text-black
font-semibold
hover:bg-gray-200
transition
">

                                            {{ $banner->button_name }}

                                        </a>
                                    @endif



                                </div>


                            </div>


                        </div>


                    </div>
                @endforeach


            </div>




            <button @click="prev()"
                class="
absolute
left-4
top-1/2
-translate-y-1/2
bg-white/20
text-white
rounded-full
w-10
h-10
">

                ‹

            </button>



            <button @click="next()"
                class="absolute right-4
top-1/2
-translate-y-1/2
bg-white/20
text-white
rounded-full
w-10
h-10
">

                ›

            </button>




        </div>


    @endif

</section>
