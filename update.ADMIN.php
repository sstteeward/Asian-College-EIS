<?php
include 'db.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id']; 
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $position = $_POST['position'];
    $sex = $_POST['sex'];

    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $imgName = $_FILES['picture']['name'];
        $imgTmp = $_FILES['picture']['tmp_name'];
        $uploadPath = 'uploads/' . basename($imgName);

        move_uploaded_file($imgTmp, $uploadPath);
    } else {

        $getImg = $conn->prepare("SELECT picture FROM employees WHERE id = ?");
        $getImg->bind_param("s", $id);
        $getImg->execute();
        $imgResult = $getImg->get_result();
        $existing = $imgResult->fetch_assoc();
        $uploadPath = $existing['picture'];
    }

    $update = $conn->prepare("UPDATE employees SET fullName = ?, email = ?, position = ?, sex = ?, picture = ? WHERE id = ?");
    $update->bind_param("ssssss", $fullname, $email, $position, $sex, $uploadPath, $id);

    if ($update->execute()) {
        header("Location: employee.php");
        exit();
    } else {
        echo "❌ Error updating admin.";
    }
} else {
    echo "⚠️ Invalid request.";
}
?>