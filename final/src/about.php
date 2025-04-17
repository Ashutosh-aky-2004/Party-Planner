<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About us</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
</head>

<body class="w-full h-full bg-black">

    <?php require 'essentials/header.php'; ?>

    <!-- Body -->
    <!-- Body Content (from Body to Footer content) -->
    <div class="sm:flex items-center max-w-screen-xl">
        <div class="sm:w-1/2 p-10 animate__animated animate__fadeIn animate__delay-0.5s">
            <div class="image object-center text-center">
                <img src="ppl.png" class="transform hover:scale-105 transition-transform duration-500">
            </div>
        </div>
        <div class="sm:w-1/2 p-5 animate__animated animate__fadeIn animate__delay-0.5s">
            <div class="text border-b-4 border-white pb-4">
                <h2 class="my-4 font-bold text-3xl sm:text-4xl text-white animate__animated animate__fadeInUp animate__delay-0.5s">About <span class="text-white">EVENTIFY</span></h2>
                <p class="text-white animate__animated animate__fadeInUp animate__delay-0.5s">
                    At EVENTIFY, we specialize in transforming ordinary moments into extraordinary celebrations. Whether it’s a birthday, wedding, corporate event, or any special occasion, our team is dedicated to curating unforgettable experiences that exceed your expectations.
                </p>
            </div>
        </div>
    </div>

    <div class="bg-black">
        <!-- Mission and Values Section -->
        <section class="text-center py-12 px-4 animate__animated animate__fadeIn animate__delay-1s">
            <h2 class="text-2xl font-bold text-white animate__animated animate__fadeInUp">Mission and Values</h2>
            <p class="mt-4 text-white max-w-2xl mx-auto animate__animated animate__fadeInUp animate__delay-1s">
                Our mission is to bring people together through unforgettable events, providing creativity, reliability, and impeccable support every step of the way.
            </p>
            <div class="flex justify-center space-x-8 mt-8 animate__animated animate__fadeInUp animate__delay-1s">
                <div class="transition transform hover:scale-110 border-4 border-white rounded-lg p-6">
                    <h3 class="text-xl font-bold text-white">100+</h3>
                    <p class="text-white">Events Planned</p>
                </div>
                <div class="transition transform hover:scale-110 border-4 border-white rounded-lg p-6">
                    <h3 class="text-xl font-bold text-white">10+</h3>
                    <p class="text-white">Years of Experience</p>
                </div>
            </div>
        </section>

        <!-- Our Vision Section -->
        <section class="bg-black text-white py-12 px-4 animate__animated animate__fadeIn animate__delay-1s">
            <h2 class="text-2xl font-bold text-center">Our Vision</h2>
            <p class="mt-4 text-center max-w-2xl mx-auto animate__animated animate__fadeInUp animate__delay-1.5s">
                We envision creating unforgettable experiences, making every event an extraordinary memory. We are committed to delivering high-quality events tailored to your unique needs.
            </p>
        </section>

        <!-- Event Services Section -->
        <section class="text-center py-12 px-4 animate__animated animate__fadeIn animate__delay-1s">
    <h2 class="text-2xl font-bold text-white">Our Event Services</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 mt-8 animate__animated animate__fadeInUp animate__delay-2s">
        <div class="p-4 shadow-lg rounded-lg text-white bg-black border-4 border-white hover:bg-white hover:text-black transition-all transform hover:scale-110">
            <h3 class="text-xl font-bold ">Weddings</h3>
        </div>
        <div class="p-4 shadow-lg rounded-lg text-white bg-black border-4 border-white hover:bg-white hover:text-black transition-all transform hover:scale-110">
            <h3 class="text-xl font-bold ">Corporate Events</h3>
        </div>
        <div class="p-4 shadow-lg rounded-lg text-white bg-black border-4 border-white hover:bg-white hover:text-black transition-all transform hover:scale-110">
            <h3 class="text-xl font-bold ">Birthday Parties</h3>
        </div>
        <div class="p-4 shadow-lg rounded-lg text-white bg-black border-4 border-white hover:bg-white hover:text-black transition-all transform hover:scale-110">
            <h3 class="text-xl font-bold ">Conferences</h3>
        </div>
    </div>
</section>


        <!-- Our Expertise Section -->
        <section class="bg-black py-12 px-4 animate__animated animate__fadeIn animate__delay-1s">
            <h2 class="text-2xl font-bold text-center text-white">Our Expertise</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8 max-w-5xl mx-auto animate__animated animate__fadeInUp animate__delay-2s">
                <div class="p-4 shadow-lg rounded-lg bg-black hover:shadow-xl transition-shadow transform hover:scale-105 border-4 border-gray-400">
                    <h3 class="text-xl font-bold text-white">Event Planning</h3>
                    <p class="text-white mt-2">From concept to completion, we ensure your event is flawless.</p>
                </div>
                <div class="p-4 shadow-lg rounded-lg bg-black hover:shadow-xl transition-shadow transform hover:scale-105 border-4 border-gray-400">
                    <h3 class="text-xl font-bold text-white">Venue Management</h3>
                    <p class="text-white mt-2">Finding the perfect venue that suits your event's needs.</p>
                </div>
                <div class="p-4 shadow-lg rounded-lg bg-black hover:shadow-xl transition-shadow transform hover:scale-105 border-4 border-gray-400">
                    <h3 class="text-xl font-bold text-white">Catering Services</h3>
                    <p class="text-white mt-2">Top-notch catering services to delight your guests.</p>
                </div>
                <div class="p-4 shadow-lg rounded-lg bg-black hover:shadow-xl transition-shadow transform hover:scale-105 border-4 border-gray-400">
                    <h3 class="text-xl font-bold text-white">Event Promotion</h3>
                    <p class="text-white mt-2">We help promote your event for maximum reach and success.</p>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="bg-black text-white text-center py-12 px-4 animate__animated animate__fadeIn animate__delay-1s">
            <h2 class="text-2xl font-bold">Client Testimonials</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8 mt-8 max-w-5xl mx-auto animate__animated animate__fadeInUp animate__delay-2s">
                <div class="p-4 shadow-lg rounded-lg bg-black border-4 border-white hover:bg-white hover:text-black transition-colors transform hover:scale-105">
                    <p>"Eventify made our wedding unforgettable! Their attention to detail was incredible."</p>
                    <h3 class="mt-4 font-bold">- Client A</h3>
                </div>
                <div class="p-4 shadow-lg rounded-lg bg-black border-4 border-white hover:bg-white hover:text-black transition-colors transform hover:scale-105">
                    <p>"The corporate event was seamless, thanks to Eventify’s outstanding coordination."</p>
                    <h3 class="mt-4 font-bold">- Client B</h3>
                </div>
                <div class="p-4 shadow-lg rounded-lg bg-black border-4 border-white hover:bg-white hover:text-black transition-colors transform hover:scale-105">
                    <p>"I can’t thank Eventify enough for making my 30th birthday such a success!"</p>
                    <h3 class="mt-4 font-bold">- Client C</h3>
                </div>
            </div>
        </section>

        <!-- FAQ Section -->
        <section class="text-center py-12 px-4 w-full animate__animated animate__fadeIn animate__delay-1s">
            <h2 class="text-2xl font-bold text-white">Frequently Asked Questions</h2>
            <div class="mt-8 animate__animated animate__fadeInUp animate__delay-2s">
                <div class="p-4 border-4 border-white rounded-lg shadow-md transition transform hover:scale-100 scale-90 bg-black">
                    <h3 class="text-xl font-bold text-white">How do I start planning my event with Eventify?</h3>
                    <p class="mt-2 text-white">Simply reach out to us through our website or call our team to begin the planning process.</p>
                </div>
                <div class="p-4 border-4 border-white rounded-lg shadow-md transition transform hover:scale-100 scale-90 mt-4 bg-black">
                    <h3 class="text-xl font-bold text-white">What types of events do you specialize in?</h3>
                    <p class="mt-2 text-white">We specialize in weddings, corporate events, conferences, and private parties.</p>
                </div>
                <div class="p-4 border-4 border-white rounded-lg shadow-md transition transform hover:scale-100 scale-90 mt-4 bg-black">
                    <h3 class="text-xl font-bold text-white">Do you provide event coordination on the day of the event?</h3>
                    <p class="mt-2 text-white">Yes, we offer full event coordination to ensure everything goes smoothly on the big day.</p>
                </div>
            </div>
        </section>

        <section class="text-center py-12 px-4">
            <h2 class="text-2xl font-bold text-white mb-8">Meet Our Team</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-5">
                <!-- Member 1 -->
                <div class="p-4 bg-black rounded-lg shadow-lg border-4 border-white transition-transform transform hover:scale-105">
                    <div class="member-image mb-4">
                        <img src="member1.jpg" alt="Member 1" class="w-full h-80 object-cover rounded-lg">
                    </div>
                    <h3 class="text-xl font-bold text-white">Sameer Swami</h3>
                    <p class="text-white mt-2">CEO & Founder</p>
                </div>

                <!-- Member 2 -->
                <div class="p-4 bg-black rounded-lg shadow-lg border-4 border-white transition-transform transform hover:scale-105">
                    <div class="member-image mb-4">
                        <img src="member2.jpeg" alt="Member 2" class="w-full h-80 object-cover rounded-lg">
                    </div>
                    <h3 class="text-xl font-bold text-white">Ashutosh Kumar Yadav</h3>
                    <p class="text-white mt-2">Lead Designer</p>
                </div>

                <!-- Member 3 -->
                <div class="p-4 bg-black rounded-lg shadow-lg border-4 border-white transition-transform transform hover:scale-105">
                    <div class="member-image mb-4">
                        <img src="member3.avif" alt="Member 3" class="w-full h-80 object-cover rounded-lg">
                    </div>
                    <h3 class="text-xl font-bold text-white">Sanskar Katiyar</h3>
                    <p class="text-white mt-2">Founder</p>
                </div>

                <!-- Member 4 -->
                <div class="p-4 bg-black rounded-lg shadow-lg border-4 border-white transition-transform transform hover:scale-105">
                    <div class="member-image mb-4">
                        <img src="member4.jpeg" alt="Member 3" class="w-full h-80 object-cover rounded-lg">
                    </div>
                    <h3 class="text-xl font-bold text-white">Jitendra Kumar Gupta</h3>
                    <p class="text-white mt-2">Founder</p>
                </div>
            </div>
        </section>

    </div>

    <!-- Footer Section -->
    <?php require 'essentials/footer.html'; ?>

</body>

</html>
