<?php
$title = 'Youdemy - ' . htmlspecialchars($course->getTitle());
ob_start();
?>

<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden slide-up">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-12 text-white">
        <h1 class="text-4xl font-bold mb-4"><?php echo htmlspecialchars($course->getTitle()); ?></h1>
        <div class="flex items-center space-x-4 text-sm">
            <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                8 hours of content
            </span>
            <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Certificate of completion
            </span>
        </div>
    </div>

    <div class="p-8">
        <div class="prose max-w-none">
            <h2 class="text-2xl font-semibold mb-4">About this course</h2>
            <p class="text-gray-600 leading-relaxed mb-8">
                <?php echo htmlspecialchars($course->getDescription()); ?>
            </p>
        </div>

        <div class="border-t border-gray-200 pt-8">
            <h3 class="text-xl font-semibold mb-4">What you'll learn</h3>
            <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <li class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Comprehensive understanding of the subject
                </li>
                <li class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Practical examples and exercises
                </li>
                <li class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Real-world applications
                </li>
                <li class="flex items-center text-gray-600">
                    <svg class="w-5 h-5 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Lifetime access to course materials
                </li>
            </ul>
        </div>

        <div class="mt-8">
            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'student'): ?>
                <a 
                    href="index.php?action=enroll&id=<?php echo $course->getId(); ?>" 
                    class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300 transform hover:-translate-y-1"
                >
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Enroll in this course
                </a>
            <?php elseif (!isset($_SESSION['user'])): ?>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                    <p class="text-gray-600 mb-4">Please log in to enroll in this course</p>
                    <a 
                        href="index.php?action=login" 
                        class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all duration-300"
                    >
                        Sign in to continue
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'layout.php';
?>
