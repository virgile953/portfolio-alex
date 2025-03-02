import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth } = usePage<SharedData>().props;

    return (
        <>
            <Head title="Welcome">
                <link rel="preconnect" href="https://fonts.bunny.net" />
                <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
            </Head>
            <div className="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a] dark:text-[#EDEDEC]">
                <div className="w-full max-w-4xl">
                    <header className="mb-12 flex justify-end">
                        <nav className="flex items-center gap-4">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    Dashboard
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                    >
                                        Register
                                    </Link>
                                </>
                            )}
                        </nav>
                    </header>

                    <main className="flex flex-col items-center justify-center">
                        <h1 className="mb-6 text-center text-5xl font-bold tracking-tight text-[#1b1b18] dark:text-[#EDEDEC]">
                            Welcome to Coucou
                        </h1>

                        <div className="mx-auto max-w-2xl rounded-lg border border-[#19140035] bg-white p-8 shadow-sm dark:border-[#3E3E3A] dark:bg-[#161615]">
                            <p className="mb-4 text-center text-lg text-[#706f6c] dark:text-[#A1A09A]">
                                A simple and elegant platform for your projects.
                            </p>

                            <p className="text-center text-[#706f6c] dark:text-[#A1A09A]">
                                Get started by logging in or creating a new account.
                            </p>

                            {!auth.user && (
                                <div className="mt-6 flex justify-center gap-4">
                                    <Link
                                        href={route('login')}
                                        className="inline-block rounded-sm border border-transparent bg-[#1b1b18] px-6 py-2 text-sm font-medium text-white hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:bg-white"
                                    >
                                        Log in
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="inline-block rounded-sm border border-[#19140035] px-6 py-2 text-sm font-medium text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                    >
                                        Register
                                    </Link>
                                </div>
                            )}
                        </div>
                    </main>
                </div>
            </div>
        </>
    );
}
