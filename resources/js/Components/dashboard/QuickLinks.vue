<script setup>
import { Link } from '@inertiajs/vue3'

const actions = [
  {
    name: 'Thêm trẻ mới',
    description: 'Thêm hồ sơ trẻ vào hệ thống can thiệp',
    href: '/children/create',
    color: 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100/80',
    icon: `<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
    </svg>`
  },
  {
    name: 'Lên lịch tập hằng ngày',
    description: 'Tạo lịch can thiệp phù hợp cho trẻ',
    href: '/training/create',
    color: 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100/80',
    icon: `<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
    </svg>`
  },
  {
    name: 'Thêm mẫu bài tập',
    description: 'Thêm bài tập vận động hoặc nhận thức vào thư viện',
    href: '/exercises/create',
    color: 'bg-blue-50 text-blue-700 hover:bg-blue-100/80',
    icon: `<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>`
  },
  {
    name: 'Ghi nhận hành vi',
    description: 'Theo dõi hành vi ăn vạ, bùng nổ hoặc tương tác xã hội',
    href: '/behavior',
    color: 'bg-amber-50 text-amber-700 hover:bg-amber-100/80',
    icon: `<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
    </svg>`
  },
  {
    name: 'Thực hiện đánh giá',
    description: 'Đánh giá tiến bộ vận động, ngôn ngữ và nhận thức',
    href: '/assessment',
    color: 'bg-purple-50 text-purple-700 hover:bg-purple-100/80',
    icon: `<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.375M9 18h3.375m7.5 0V3.75A2.25 2.25 0 0017.625 1.5H6.375A2.25 2.25 0 004.125 3.75V18a2.25 2.25 0 002.25 2.25h11.25A2.25 2.25 0 0019.875 18zM9.75 8.25A1.5 1.5 0 118.25 6.75a1.5 1.5 0 011.5 1.5z" />
    </svg>`
  },
  {
    name: 'Xem phân tích',
    description: 'Tạo báo cáo và xem biểu đồ tiến bộ của trẻ',
    href: '/reports',
    color: 'bg-rose-50 text-rose-700 hover:bg-rose-100/80',
    icon: `<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
      <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
    </svg>`
  }
]
</script>

<template>
  <div class="rounded-xl border border-gray-100 bg-white p-6 shadow-sm">
    <div class="border-b border-gray-50 pb-4">
      <h3 class="text-base font-bold text-gray-900">Thao tác nhanh</h3>
      <p class="text-xs text-gray-500">Các phím tắt và công cụ can thiệp thường dùng</p>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <Link 
        v-for="action in actions" 
        :key="action.name" 
        :href="action.href" 
        class="flex items-start gap-4 rounded-xl border border-gray-50 p-4 transition-all duration-200 hover:-translate-y-0.5 hover:border-gray-100 hover:shadow-sm"
        :class="action.color"
      >
        <div class="flex-shrink-0" v-html="action.icon"></div>
        <div>
          <h4 class="text-sm font-bold text-gray-900">{{ action.name }}</h4>
          <p class="mt-1 text-xs text-gray-500 line-clamp-2 leading-relaxed">{{ action.description }}</p>
        </div>
      </Link>
    </div>
  </div>
</template>
