<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Eventify</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="w-full h-full bg-gray-300">

  <?php require 'essentials/header.php'; ?>


  <div class="w-full h-screen relative">
    <!-- Background Video -->
    <div class=" inset-0 z-0">
      <video class="w-full h-full object-cover" autoplay muted loop>
        <source src="bg2.mp4" type="video/mp4" />
      </video>
    </div>

    <!-- Overlay Content -->
    <div class="absolute inset-0 z-10 flex flex-col items-center justify-center text-center space-y-10 px-4">
      <h1 class="text-[60px] md:text-[80px] font-bold text-white font-serif w-2/3">
        All Your Party Planning Needs In One Place
      </h1>
      <a href="index.php">
        <button type="submit"
          class="flex justify-center gap-2 items-center shadow-xl text-lg bg-gray-50 backdrop-blur-md lg:font-semibold isolation-auto border-gray-50 before:absolute before:w-full before:transition-all before:duration-700 before:hover:w-full before:-left-full before:hover:left-0 before:rounded-full before:bg-emerald-500 hover:text-gray-50 before:-z-10 before:aspect-square before:hover:scale-150 before:hover:duration-700 relative px-6 py-3 overflow-hidden border-2 rounded-full group">
          Explore
          <svg class="w-8 h-8 group-hover:rotate-90 group-hover:bg-gray-50 text-gray-50 ease-linear duration-300 rounded-full border border-gray-700 group-hover:border-none p-2 rotate-45"
            viewBox="0 0 16 19" xmlns="http://www.w3.org/2000/svg">
            <path
              d="M7 18C7 18.5523 7.44772 19 8 19C8.55228 19 9 18.5523 9 18H7ZM8.70711 0.292893C8.31658 -0.0976311 7.68342 -0.0976311 7.29289 0.292893L0.928932 6.65685C0.538408 7.04738 0.538408 7.68054 0.928932 8.07107C1.31946 8.46159 1.95262 8.46159 2.34315 8.07107L8 2.41421L13.6569 8.07107C14.0474 8.46159 14.6805 8.46159 15.0711 8.07107C15.4616 7.68054 15.4616 7.04738 15.0711 6.65685L8.70711 0.292893ZM9 18L9 1H7L7 18H9Z"
              class="fill-gray-800 group-hover:fill-gray-800"></path>
          </svg>
        </button>
      </a>
    </div>
  </div>
  <?php  ?>


</body>

</html>