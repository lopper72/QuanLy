<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Trang chào mừng" />

    <main class="min-h-screen bg-slate-50 text-slate-900">
        <div class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-6 py-12">
            <section class="space-y-6">
                <p class="text-sm font-semibold text-blue-700">
                    Hệ thống quản lý can thiệp
                </p>
                <h1 class="max-w-3xl text-4xl font-bold text-slate-950 sm:text-5xl">
                    Quản lý hồ sơ trẻ, buổi tập, đánh giá và báo cáo tiến bộ
                </h1>
                <p class="max-w-2xl text-base leading-7 text-slate-600">
                    Theo dõi hoạt động hằng ngày, ghi nhận hành vi và tổng hợp tiến bộ trong một giao diện thống nhất.
                </p>

                <nav v-if="canLogin" class="flex flex-wrap gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800"
                    >
                        Vào bảng điều khiển
                    </Link>

                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="rounded-md bg-blue-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-800"
                        >
                            Đăng nhập
                        </Link>

                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-100"
                        >
                            Đăng ký
                        </Link>
                    </template>
                </nav>
            </section>
        </div>
    </main>
</template>
