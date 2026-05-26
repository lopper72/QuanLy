<template>
  <div class="min-h-screen bg-gray-100 font-sans leading-normal">
    <nav class="bg-indigo-600 text-white shadow-md">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <div class="flex min-w-0 items-center">
            <span class="shrink-0 text-xl font-bold whitespace-nowrap">Hệ thống Can thiệp</span>
            <div class="ml-8 hidden items-baseline space-x-2 lg:flex">
              <!-- Main Nav Items -->
              <a v-for="item in mainNav" :key="item.href" :href="item.href" :class="navClass(item.href)">
                <component :is="item.icon" v-if="item.icon" class="h-4 w-4 shrink-0" />
                <span class="whitespace-nowrap">{{ item.label }}</span>
              </a>

              <!-- "More" Dropdown Menu -->
              <div class="relative">
                <button
                  type="button"
                  class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-2 text-sm font-medium transition duration-150 ease-in-out cursor-pointer whitespace-nowrap"
                  :class="isMoreActive ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-500 hover:text-white'"
                  @click="isMoreOpen = !isMoreOpen"
                >
                  <span>Thêm</span>
                  <svg class="h-4 w-4 transition-transform duration-150" :class="{ 'rotate-180': isMoreOpen }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                  </svg>
                </button>

                <!-- Fullscreen Overlay to close dropdown on click outside -->
                <div v-if="isMoreOpen" class="fixed inset-0 z-40 cursor-default" @click="isMoreOpen = false"></div>

                <!-- Dropdown Options -->
                <transition
                  enter-active-class="transition ease-out duration-100"
                  enter-from-class="transform opacity-0 scale-95"
                  enter-to-class="transform opacity-100 scale-100"
                  leave-active-class="transition ease-in duration-75"
                  leave-from-class="transform opacity-100 scale-100"
                  leave-to-class="transform opacity-0 scale-95"
                >
                  <div
                    v-show="isMoreOpen"
                    class="absolute left-0 lg:right-0 lg:left-auto z-50 mt-2 w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                  >
                    <a
                      v-for="item in moreNav"
                      :key="item.href"
                      :href="item.href"
                      class="flex items-center gap-2 px-4 py-2.5 text-sm transition duration-150 ease-in-out whitespace-nowrap"
                      :class="isActive(item.href) ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'"
                      @click="isMoreOpen = false"
                    >
                      <component :is="item.icon" v-if="item.icon" class="h-4 w-4 shrink-0" />
                      <span>{{ item.label }}</span>
                    </a>
                  </div>
                </transition>
              </div>
            </div>
          </div>

          <div class="lg:hidden">
            <button
              type="button"
              class="inline-flex items-center gap-1.5 justify-center rounded-md p-2 text-indigo-200 transition duration-150 ease-in-out hover:bg-indigo-500 hover:text-white focus:outline-none cursor-pointer"
              @click="isOpen = !isOpen"
            >
              <span class="text-sm font-medium whitespace-nowrap">{{ isOpen ? 'Đóng' : 'Menu' }}</span>
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ hidden: isOpen, 'inline-flex': !isOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ hidden: !isOpen, 'inline-flex': isOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div :class="{ block: isOpen, hidden: !isOpen }" class="border-t border-indigo-500 bg-indigo-600 lg:hidden">
        <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
          <a v-for="item in mobileNavItems" :key="item.href" :href="item.href" :class="mobileNavClass(item.href)">
            <component :is="item.icon" v-if="item.icon" class="h-4 w-4 shrink-0" />
            <span>{{ item.label }}</span>
          </a>
        </div>
      </div>
    </nav>

    <main class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <slot />
      </div>
    </main>
  </div>
</template>

<script setup>
import { h, ref, computed } from 'vue';

const isOpen = ref(false);
const isMoreOpen = ref(false);

const MessageIcon = {
  render() {
    return h('svg', {
      class: 'h-4 w-4',
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      'aria-hidden': 'true',
    }, [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.77 9.77 0 0 1-4-.83L3 20l1.23-3.07A7.32 7.32 0 0 1 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z',
      }),
    ]);
  },
};

const mainNav = [
  { href: '/dashboard', label: 'Tổng quan', icon: null },
  { href: '/children', label: 'Trẻ em', icon: null },
  { href: '/training', label: 'Huấn luyện', icon: null },
  { href: '/exercises', label: 'Bài tập', icon: null },
  { href: '/assessment', label: 'Đánh giá', icon: null },
  { href: '/behavior', label: 'Hành vi', icon: null },
  { href: '/reports', label: 'Báo cáo', icon: null },
];

const moreNav = [
  { href: '/supplements', label: 'Lịch bổ sung', icon: null },
  { href: '/meal-plans', label: 'Lịch ăn uống', icon: null },
  { href: '/telegram', label: 'Tin nhắn Telegram', icon: MessageIcon },
  { href: '/settings', label: 'Cài đặt', icon: null },
];

const mobileNavItems = [...mainNav, ...moreNav];

const isActive = (path) => {
  if (typeof window === 'undefined') {
    return false;
  }

  return window.location.pathname === path || window.location.pathname.startsWith(`${path}/`);
};

const isMoreActive = computed(() => {
  return moreNav.some(item => isActive(item.href));
});

const navClass = path => [
  isActive(path) ? 'bg-indigo-700 text-white font-semibold' : 'text-indigo-200 hover:bg-indigo-500 hover:text-white',
  'inline-flex items-center gap-1.5 rounded-md px-2.5 py-2 text-sm font-medium transition duration-150 ease-in-out whitespace-nowrap',
];

const mobileNavClass = path => [
  isActive(path) ? 'bg-indigo-700 text-white font-semibold' : 'text-indigo-100 hover:bg-indigo-500 hover:text-white',
  'flex items-center gap-2 rounded-md px-3 py-2 text-base font-medium whitespace-nowrap',
];
</script>
