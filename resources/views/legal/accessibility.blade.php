@extends('layouts.app')

@section('title', 'Accessibility Statement')

@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 max-w-4xl">
    <div class="bg-white dark:bg-gray-800 p-8 rounded-xl shadow-lg border border-gray-100 dark:border-gray-700">
        <h1 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-6 border-b dark:border-gray-700 pb-2">
            Accessibility Statement
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-8">
            Committed to an inclusive shopping experience.
        </p>

        <div class="space-y-6 text-gray-700 dark:text-gray-300">
            <p>
                E-SHOP is committed to ensuring digital accessibility for people with disabilities. We are continually improving the user experience for everyone and applying the relevant accessibility standards.
            </p>

            <h2 class="text-2xl font-bold text-gray-900 dark:text-white pt-4">1. Conformance Status</h2>
            <p>
                We aim to conform to the Web Content Accessibility Guidelines (WCAG) 2.1 Level AA, a framework used to define requirements for improving web accessibility. Our ongoing efforts include regular audits and user testing.
            </p>

            <h2 class="2xl font-bold text-gray-900 dark:text-white pt-4">2. Technical Specifications (Bogus Content)</h2>
            <p>
                The E-SHOP platform is built using standard HTML5, CSS3, and JavaScript, leveraging ARIA landmarks and proper tab indexing. Screen reader compatibility is maintained by ensuring semantic tags are used for navigation and controls. The color contrast ratio exceeds 4.5:1, except where exempted by WCAG guidelines, such as decorative elements. We also enforce keyboard-only navigation compatibility.
            </p>

            <h2 class="2xl font-bold text-gray-900 dark:text-white pt-4">3. Feedback</h2>
            <p>
                We welcome your feedback on the accessibility of the E-SHOP website. Please let us know if you encounter accessibility barriers on E-SHOP by contacting us through the provided support channels.
            </p>
        </div>
    </div>
</div>
@endsection