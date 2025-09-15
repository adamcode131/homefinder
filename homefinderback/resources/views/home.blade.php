<html><head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script><script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ['Poppins', 'sans-serif']
            }
          }
        }
      };
    </script>
    <script> window.FontAwesomeConfig = { autoReplaceSvg: 'nest'};</script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <style>
        ::webkit-scrollbar { display: none;}
        body { font-family: 'Poppins', sans-serif; }
        .search-glow { box-shadow: 0 0 0 4px rgba(30, 136, 229, 0.1); }
        .gradient-bg { background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); }
        .feature-card:hover { transform: translateY(-4px); transition: all 0.3s ease; }
        .suggestion-pulse:hover { animation: pulse 0.6s ease-in-out; }
        @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.05); } }
    </style>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin=""><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;500;600;700;800;900&amp;display=swap"><style>
  .highlighted-section {
    outline: 2px solid #3F20FB;
    background-color: rgba(63, 32, 251, 0.1);
  }

  .edit-button {
    position: absolute;
    z-index: 1000;
  }

  ::-webkit-scrollbar {
    display: none;
  }

  html, body {
    -ms-overflow-style: none;
    scrollbar-width: none;
  }
  </style></head>
<body class="bg-gray-50">

<div id="main-container" class="min-h-[900px] bg-gradient-to-br from-slate-50 via-white to-blue-50">
  <!-- Header -->
  <header id="header" class="w-full px-8 py-5 flex items-center justify-between bg-white/80 backdrop-blur-sm border-b border-slate-200/60 sticky top-0 z-50">
    <!-- Logo -->
    <div class="flex items-center group">
      <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg group-hover:shadow-xl transition-all duration-300">
        <i class="fa-solid fa-home text-white text-lg"></i>
      </div>
      <span class="ml-3 text-2xl font-semibold bg-gradient-to-r from-slate-800 to-slate-600 bg-clip-text text-transparent">HomeFinder</span>
    </div>
    
    <!-- Right side navigation -->
    <div class="flex items-center space-x-6">
      <!-- Language Selector -->
      <div class="relative">
        <select class="bg-white/90 border border-slate-300 rounded-xl px-4 py-2.5 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-blue-500/30 focus:border-blue-500 transition-all duration-200 shadow-sm hover:shadow-md">
          <option value="fr">🇫🇷 FR</option>
          <option value="en">🇺🇸 EN</option>
          <option value="ar">🇲🇦 AR</option>
        </select>
      </div>
      
      <!-- Login/Signup -->
      <div class="flex items-center space-x-3">
        <button class="text-slate-600 hover:text-slate-900 px-4 py-2.5 text-sm font-medium transition-all duration-200 hover:bg-slate-100 rounded-lg">
          Login
        </button>
        <span class="text-slate-300">|</span>
        <button class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-6 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
          Sign Up
        </button>
      </div>
    </div>
  </header>

  <!-- Main Content -->
  <main id="main-content" class="flex flex-col items-center justify-center px-8 py-24">
    <!-- Hero Logo Section -->
    <div class="mb-12 text-center">
      <div class="flex items-center justify-center mb-6">
        <div class="w-20 h-20 bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 rounded-3xl flex items-center justify-center shadow-2xl transform hover:scale-105 transition-all duration-300">
          <i class="fa-solid fa-home text-white text-3xl"></i>
        </div>
      </div>
      <h1 class="text-5xl font-bold bg-gradient-to-r from-slate-800 via-slate-700 to-slate-600 bg-clip-text text-transparent mb-3">HomeFinder</h1>
      <p class="text-slate-500 text-lg font-light">Powered by Artificial Intelligence</p>
    </div>

    <!-- Search Section -->
    <div id="search-section" class="w-full max-w-3xl">
      <!-- Search Bar -->
      <div class="relative mb-8">
        <div class="flex items-center bg-white border-2 border-slate-200 rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 focus-within:border-blue-500 focus-within:search-glow group">
          <div class="pl-8 pr-4">
            <i class="fa-solid fa-search text-slate-400 text-xl group-focus-within:text-blue-500 transition-colors duration-200"></i>
          </div>
          <input type="text" placeholder="Ex: 2 chambres, 1 salon, quartier Gauthier" class="flex-1 py-6 px-3 text-xl text-slate-700 bg-transparent focus:outline-none placeholder-slate-400 font-light">
          <button class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-10 py-4 rounded-xl mr-3 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 font-medium">
            <i class="fa-solid fa-search mr-2"></i>Search
          </button>
        </div>
      </div>

      <!-- Tagline -->
      <p class="text-center text-slate-600 text-xl mb-12 font-light">
        Find your home with <span class="font-medium text-blue-600">AI-powered search</span>
      </p>

      <!-- Quick Suggestions -->
      <div class="flex flex-wrap justify-center gap-4 mb-16">
        <button class="suggestion-pulse bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 px-6 py-3 rounded-2xl text-sm border-2 border-slate-200 hover:border-blue-300 transition-all duration-200 shadow-md hover:shadow-lg">
          <i class="fa-solid fa-bed mr-2 text-blue-500"></i>2 Bedrooms
        </button>
        <button class="suggestion-pulse bg-white hover:bg-green-50 text-slate-700 hover:text-green-700 px-6 py-3 rounded-2xl text-sm border-2 border-slate-200 hover:border-green-300 transition-all duration-200 shadow-md hover:shadow-lg">
          <i class="fa-solid fa-location-dot mr-2 text-green-500"></i>Gauthier
        </button>
        <button class="suggestion-pulse bg-white hover:bg-yellow-50 text-slate-700 hover:text-yellow-700 px-6 py-3 rounded-2xl text-sm border-2 border-slate-200 hover:border-yellow-300 transition-all duration-200 shadow-md hover:shadow-lg">
          <i class="fa-solid fa-coins mr-2 text-yellow-500"></i>Under 5000 DH
        </button>
        <button class="suggestion-pulse bg-white hover:bg-purple-50 text-slate-700 hover:text-purple-700 px-6 py-3 rounded-2xl text-sm border-2 border-slate-200 hover:border-purple-300 transition-all duration-200 shadow-md hover:shadow-lg">
          <i class="fa-solid fa-car mr-2 text-purple-500"></i>With Parking
        </button>
      </div>
    </div>

    <!-- Features Section -->
    <div id="features-section" class="w-full max-w-6xl mt-20">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-10">
        <!-- AI-Powered -->
        <div class="feature-card text-center p-8 bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-slate-100">
          <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <i class="fa-solid fa-brain text-blue-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-slate-800 mb-3">AI-Powered Search</h3>
          <p class="text-slate-600 text-base leading-relaxed">Smart algorithms understand your preferences and find the perfect match for your dream home</p>
        </div>

        <!-- Easy Booking -->
        <div class="feature-card text-center p-8 bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-slate-100">
          <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-green-200 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <i class="fa-solid fa-calendar-check text-green-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-slate-800 mb-3">Easy Booking</h3>
          <p class="text-slate-600 text-base leading-relaxed">Schedule property visits with just one click, no hassle or complicated procedures</p>
        </div>

        <!-- Verified Properties -->
        <div class="feature-card text-center p-8 bg-white rounded-3xl shadow-lg hover:shadow-2xl transition-all duration-300 border border-slate-100">
          <div class="w-16 h-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg">
            <i class="fa-solid fa-shield-check text-emerald-600 text-2xl"></i>
          </div>
          <h3 class="text-xl font-semibold text-slate-800 mb-3">Verified Properties</h3>
          <p class="text-slate-600 text-base leading-relaxed">All listings are verified by our expert team for your complete peace of mind</p>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer id="footer" class="border-t border-slate-200 bg-white/60 backdrop-blur-sm py-12 px-8 mt-24">
    <div class="max-w-7xl mx-auto">
      <div class="flex flex-col md:flex-row justify-between items-center">
        <div class="flex items-center mb-6 md:mb-0">
          <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center mr-3 shadow-md">
            <i class="fa-solid fa-home text-white text-sm"></i>
          </div>
          <span class="text-xl font-semibold text-slate-800">HomeFinder</span>
        </div>
        
        <div class="flex items-center space-x-8 text-sm text-slate-600 mb-6 md:mb-0">
          <span class="hover:text-blue-600 transition-colors cursor-pointer font-medium">About</span>
          <span class="hover:text-blue-600 transition-colors cursor-pointer font-medium">Privacy</span>
          <span class="hover:text-blue-600 transition-colors cursor-pointer font-medium">Terms</span>
          <span class="hover:text-blue-600 transition-colors cursor-pointer font-medium">Contact</span>
        </div>
        
        <div class="text-sm text-slate-500 font-light">
          © 2025 HomeFinder. All rights reserved.
        </div>
      </div>
    </div>
  </footer>
</div>


</body></html>

