<template>
  <div class="min-h-screen bg-gray-100 font-sans leading-normal">
    <nav class="bg-indigo-600 text-white shadow-md">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">
          <div class="flex items-center">
            <span class="text-xl font-bold">Hệ thống Can thiệp</span>
            <div class="ml-10 hidden items-baseline space-x-4 md:flex">
              <a v-for="item in navItems" :key="item.href" :href="item.href" :class="navClass(item.href)">
                <component :is="item.icon" v-if="item.icon" class="h-4 w-4" />
                <span>{{ item.label }}</span>
              </a>
            </div>
          </div>

          <div class="md:hidden">
            <button
              type="button"
              @click="isOpen = !isOpen"
              class="inline-flex items-center justify-center rounded-md p-2 text-indigo-200 transition duration-150 ease-in-out hover:bg-indigo-500 hover:text-white focus:outline-none"
              aria-label="Mở menu"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path :class="{ hidden: isOpen, 'inline-flex': !isOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path :class="{ hidden: !isOpen, 'inline-flex': isOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div :class="{ block: isOpen, hidden: !isOpen }" class="border-t border-indigo-500 bg-indigo-600 md:hidden">
        <div class="space-y-1 px-2 pb-3 pt-2 sm:px-3">
          <a v-for="item in navItems" :key="item.href" :href="item.href" :class="mobileNavClass(item.href)">
            <component :is="item.icon" v-if="item.icon" class="h-4 w-4" />
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
import { h, ref } from 'vue';

const isOpen = ref(false);

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

const navItems = [
  { href: '/dashboard', label: 'Tổng quan', icon: null },
  { href: '/children', label: 'Trẻ em', icon: null },
  { href: '/training', label: 'Huấn luyện', icon: null },
  { href: '/exercises', label: 'Bài tập', icon: null },
  { href: '/assessment', label: 'Đánh giá', icon: null },
  { href: '/behavior', label: 'Hành vi', icon: null },
  { href: '/reports', label: 'Báo cáo', icon: null },
  { href: '/telegram', label: 'Tin nhắn Telegram', icon: MessageIcon },
  { href: '/settings', label: 'Cài đặt', icon: null },
];

const isActive = (path) => {
  if (typeof window === 'undefined') {
    return false;
  }

  return window.location.pathname === path || window.location.pathname.startsWith(`${path}/`);
};

const navClass = (path) => [
  isActive(path) ? 'bg-indigo-700 text-white' : 'text-indigo-200 hover:bg-indigo-500 hover:text-white',
  'inline-flex items-center gap-1.5 rounded-md px-3 py-2 text-sm font-medium transition duration-150 ease-in-out',
];

const mobileNavClass = (path) => [
  isActive(path) ? 'bg-indigo-700 text-white' : 'text-indigo-100 hover:bg-indigo-500 hover:text-white',
  'flex items-center gap-2 rounded-md px-3 py-2 text-base font-medium',
];
</script>
