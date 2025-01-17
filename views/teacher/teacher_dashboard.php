<?php
$title = 'Youdemy - Teacher Dashboard';
ob_start();
?>

<h1>Teacher Dashboard</h1>

<a href="index.php?action=add_course" class="button">Add New Course</a>

<h2>Your Courses</h2>
<div id="editCourseModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg w-96">
            <h3 class="text-xl font-bold mb-4">Add Category</h3>
            <form action="index.php?action=add_category" method="POST">
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2" for="categoryName">Category Name</label>
                    <input type="text" id="categoryName" name="name" 
                           class="w-full p-2 border rounded focus:outline-none focus:ring-2 focus:ring-red-600">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" 
                            onclick="this.closest('#editCourseModal').classList.add('hidden')"
                            class="px-4 py-2 text-gray-600 hover:text-gray-800">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php if (empty($teacherCourses)): ?>
    <p>You haven't created any courses yet.</p>
<?php else: ?>
    <?php foreach ($teacherCourses as $course): ?>
        <div style="border: 1px solid #ddd; padding: 10px; margin-bottom: 10px;">
            <h3><?php echo htmlspecialchars($course['title']); ?></h3>
            <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . '...'; ?></p>
            <a href="index.php?action=course&id=<?php echo $course['id']; ?>" class="button">View Course</a>
            <button value=<?php echo $course['id']; ?> onclick="document.getElementById('editCourseModal').classList.remove('hidden') "   class="button course">Edit Course</a>
            <a href="index.php?action=delete_course&id=<?php echo $course['id']; ?>" class="button" onclick="return confirm('Are you sure you want to delete this course?')">Delete Course</a>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<script>
courses = document.querySelectorAll(".course");
courses.forEach(course => {
    course.addEventListener("click",function(){
        console.log(course.value);
    })
});
    </script>
<?php
$content = ob_get_clean();
include 'views/layout.php';
?>

