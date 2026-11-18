<div
   x-show="loaded"
   x-init="window.addEventListener('DOMContentLoaded', () => { setTimeout(() => loaded = false, 300) })"
   class="fixed left-0 top-0 z-999999 flex h-screen w-screen items-center justify-center bg-white dark:bg-gray-900"
>
   <div class="t-preloader-ring"></div>
</div>
