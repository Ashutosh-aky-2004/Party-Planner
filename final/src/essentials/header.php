<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
?>
<div class="h-auto w-full bg-black border-b border-white flex justify-between items-center px-10 py-3 ">
  <!-- Logo / Brand Name -->
  <div class="pt-2 flex ml-3 -mt-2">
    <svg class="w-8 text-white" viewBox="0 0 24 24" stroke-linejoin="round" stroke-width="2" stroke-linecap="round" stroke-miterlimit="10" stroke="currentColor" fill="none">
      <rect x="3" y="1" width="7" height="12"></rect>
      <rect x="3" y="17" width="7" height="6"></rect>
      <rect x="14" y="1" width="7" height="6"></rect>
      <rect x="14" y="11" width="7" height="12"></rect>
    </svg>
    <h1 class="text-white inline font-bold text-4xl ml-4"> EVENTIFY</h1>
  </div>
  <!-- Navigation Links -->
  <div class="flex items-center gap-16">
    <h1 class="text-xl font-semibold text-white   hover:scale-110 transition-transform">
      <a href="home.php">Home</a>
    </h1>
    <h1 class="text-xl font-semibold text-white   hover:scale-110 transition-transform">
      <a href="about.php">About</a>
    </h1>
    <h1 class="text-xl font-semibold text-white   hover:scale-110 transition-transform">
      <a href="contactUs.php">Contact Us</a>
    </h1>

    <div class="relative inline-block text-left">
      <?php if (isset($_SESSION['user_id'])) : ?>
        <!-- If User is Logged In, Show Profile Dropdown -->
        <div>
          <button type="button" class="inline-flex items-center gap-2 rounded-full px-4 py-2 border-2 border-white bg-black text-white hover:bg-gray-900 transition-colors" id="menu-button" aria-expanded="false" aria-haspopup="true">
            <span class="flex items-center justify-center w-8 h-8 rounded-full bg-[#c2185b] text-white font-bold">
              <?php echo strtoupper(substr($_SESSION['email'][0] ?? 'U', 0, 1)); ?>
            </span>
            <span class="text-sm"><?php echo htmlspecialchars(explode('@', $_SESSION['email'])[0]); ?></span>
            <svg class="-mr-1 size-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
              <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
          </button>
        </div>

        <!-- Dropdown menu -->
        <div class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-gray-900 shadow-lg ring-1 ring-gray-700 focus:outline-hidden hidden" role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1" id="profile-dropdown">
          <div class="py-2 px-4" role="none">
            <div class="text-gray-300 text-sm"><?php echo htmlspecialchars(explode('@', $_SESSION['email'])[0]); ?></div>
            <div class="text-gray-400 text-xs mt-1"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
            <div class="border-t border-gray-700 my-2"></div>
            <a href="profile.php" class="block w-full px-4 py-2 text-left text-sm text-white hover:bg-gray-800 rounded" role="menuitem" tabindex="-1"><i class="fas fa-user mr-2"></i> Profile</a>
            <a href="logout.php" class="block w-full px-4 py-2 text-left text-sm text-white hover:bg-gray-800 rounded" role="menuitem" tabindex="-1"><i class="fas fa-sign-out"></i>  Sign out</a>
          </div>
        </div>
      <?php else : ?>
        <!-- If User is NOT Logged In, Show Login & Register -->
        <div class="flex items-center gap-4 ">
          <a href="login.php" class="text-xl font-semibold text-white hover:scale-110 transition-transform">SignIn</a>
          <a href="register.php" class="text-xl font-semibold text-white hover:scale-110 ml-10 transition-transform">SignUp</a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  // Toggle dropdown visibility
  document.getElementById('menu-button').addEventListener('click', function() {
    const dropdown = document.getElementById('profile-dropdown');
    dropdown.classList.toggle('hidden');

    // Update aria-expanded attribute
    const isExpanded = this.getAttribute('aria-expanded') === 'true';
    this.setAttribute('aria-expanded', !isExpanded);
  });

  // Close dropdown when clicking outside
  document.addEventListener('click', function(event) {
    const dropdown = document.getElementById('profile-dropdown');
    const button = document.getElementById('menu-button');

    if (!button.contains(event.target) && !dropdown.contains(event.target)) {
      dropdown.classList.add('hidden');
      button.setAttribute('aria-expanded', 'false');
    }
  });
</script>
