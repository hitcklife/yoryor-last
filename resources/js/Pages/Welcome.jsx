import { Head } from '@inertiajs/react';

export default function Welcome() {
    return (
        <>
            <Head title="Welcome" />

            <div className="min-h-screen bg-gradient-to-br from-pink-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-purple-900">
                <div className="flex min-h-screen items-center justify-center p-6">
                    <div className="max-w-2xl text-center">
                        {/* Logo/Icon */}
                        <div className="mb-8">
                            <div className="mx-auto h-20 w-20 rounded-full bg-gradient-to-br from-pink-500 to-purple-600 p-1">
                                <div className="flex h-full w-full items-center justify-center rounded-full bg-white dark:bg-gray-800">
                                    <svg className="h-10 w-10 text-pink-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clipRule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {/* Title */}
                        <h1 className="mb-4 bg-gradient-to-r from-pink-600 to-purple-600 bg-clip-text text-5xl font-bold text-transparent">
                            YorYor Dating
                        </h1>

                        {/* Subtitle */}
                        <p className="mb-8 text-xl text-gray-600 dark:text-gray-300">
                            React + Inertia.js is now live! 🎉
                        </p>

                        {/* Status Card */}
                        <div className="mx-auto max-w-md rounded-2xl bg-white p-8 shadow-xl dark:bg-gray-800">
                            <div className="mb-6">
                                <div className="mb-2 flex items-center justify-between">
                                    <span className="text-sm font-medium text-gray-600 dark:text-gray-400">Migration Status</span>
                                    <span className="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-800 dark:bg-green-900 dark:text-green-100">
                                        Phase 1 Complete
                                    </span>
                                </div>
                                <div className="h-2 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                    <div className="h-full w-[10%] bg-gradient-to-r from-pink-500 to-purple-600"></div>
                                </div>
                            </div>

                            <ul className="space-y-3 text-left">
                                <li className="flex items-center text-sm">
                                    <svg className="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                    <span className="text-gray-700 dark:text-gray-300">React & Inertia.js installed</span>
                                </li>
                                <li className="flex items-center text-sm">
                                    <svg className="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                    <span className="text-gray-700 dark:text-gray-300">Vite configured for React</span>
                                </li>
                                <li className="flex items-center text-sm">
                                    <svg className="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                    <span className="text-gray-700 dark:text-gray-300">Inertia middleware ready</span>
                                </li>
                                <li className="flex items-center text-sm">
                                    <svg className="mr-2 h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                                    </svg>
                                    <span className="text-gray-700 dark:text-gray-300">Laravel Echo integrated</span>
                                </li>
                                <li className="flex items-center text-sm">
                                    <svg className="mr-2 h-5 w-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clipRule="evenodd" />
                                    </svg>
                                    <span className="text-gray-700 dark:text-gray-300">68 components to migrate</span>
                                </li>
                            </ul>

                            <div className="mt-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
                                <p className="text-xs text-gray-500 dark:text-gray-400">
                                    Next Phase: Migrate shared components (Header, Footer, Sidebar)
                                </p>
                            </div>
                        </div>

                        {/* Info */}
                        <p className="mt-8 text-sm text-gray-500 dark:text-gray-400">
                            Foundation is ready. Let's build something amazing! 💪
                        </p>
                    </div>
                </div>
            </div>
        </>
    );
}
