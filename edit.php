<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include('./inc/config.php'); // adjust if needed

if (!isset($_SESSION['user_id'])) {
  echo "<h2>You are not logged in.</h2>";
  exit;
}

$user_id = intval($_SESSION['user_id']);

// Fetch user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$uploadDir = __DIR__ . '/uploads/profile_photos/';
$uploadUrl = 'uploads/profile_photos/';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $bio = trim($_POST['bio'] ?? '');
  $gender = trim($_POST['gender'] ?? '');
  $remove_photo = $_POST['remove_photo'] ?? '0';
  $newPic = null;

  // Handle new upload
  if (!empty($_FILES['profile_photo']['name'])) {
    $file = $_FILES['profile_photo'];
    if ($file['error'] === UPLOAD_ERR_OK) {
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      $allowed = ['jpg','jpeg','png','gif','webp'];
      if (in_array($ext, $allowed)) {
        $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/','_', $file['name']);
        $dest = $uploadDir . $fileName;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
          $newPic = $uploadUrl . $fileName;
          // remove old
          if (!empty($user['profile_pic']) && file_exists(__DIR__ . '/' . $user['profile_pic'])) {
            @unlink(__DIR__ . '/' . $user['profile_pic']);
          }
        }
      }
    }
  } elseif ($remove_photo === '1') {
    if (!empty($user['profile_pic']) && file_exists(__DIR__ . '/' . $user['profile_pic'])) {
      @unlink(__DIR__ . '/' . $user['profile_pic']);
    }
    $newPic = null;
  }

  // Update query
  $sql = "UPDATE users SET name=?, phone=?, interests=?, gender=?";
  $params = [$name, $phone, $bio, $gender];
  $types = "ssss";

  if ($newPic !== null) {
    $sql .= ", profile_pic=?";
    $params[] = $newPic;
    $types .= "s";
  } elseif ($remove_photo === '1') {
    $sql .= ", profile_pic=NULL";
  }

  $sql .= " WHERE id=?";
  $params[] = $user_id;
  $types .= "i";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $stmt->close();

  // Refresh session
  $_SESSION['name'] = $name;
  $_SESSION['phone'] = $phone;
  $_SESSION['interests'] = $bio;
  $_SESSION['gender'] = $gender;
  if ($newPic !== null) $_SESSION['profile_pic'] = $newPic;
  elseif ($remove_photo === '1') unset($_SESSION['profile_pic']);

  header("Location: edit.php?success=1");
  exit;
}

$success = isset($_GET['success']);
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Profile</title>
<style>
<?php /* your full CSS kept as-is */ ?>
body {
  margin: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f9f9f9; color: #222;
  display: flex; justify-content: center; align-items: flex-start;
  height: 100vh; padding: 40px 20px;
}
.container {
  background: white; max-width: 520px; width: 100%;
  border-radius: 12px; box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  padding: 30px 25px;
}
.header { display: flex; justify-content: space-between;
  font-weight: 600; font-size: 17px; color: #222;
  margin-bottom: 30px; user-select: none;
}
.header .back { cursor: pointer; color: #555; }
.header .save-btn { cursor: pointer; color: #3a0ca3; font-weight: 700; }
.header .actions { display: flex; gap: 10px; align-items: center; }
.clear-btn { cursor: pointer; color: #b00020; font-weight: 600;
  background: transparent; border: none; padding: 6px 8px; border-radius: 6px;
}
.profile-photo-wrapper {
  position: relative; width: 100px; height: 100px; margin: 0 auto 15px;
  border-radius: 50%; overflow: hidden; box-shadow: 0 4px 8px rgba(58,12,163,0.15);
  border: 3px solid #fff; cursor: pointer; transition: box-shadow 0.3s ease;
}
.profile-photo-wrapper:hover { box-shadow: 0 0 15px #3a0ca3cc; }
.profile-photo-wrapper img { width: 100%; height: 100%; object-fit: cover; }
.camera-icon {
  position: absolute; bottom: 6px; right: 6px; background: #3a0ca3;
  border-radius: 50%; width: 30px; height: 30px; display: flex;
  justify-content: center; align-items: center; color: white;
  font-size: 17px; border: 2px solid white;
  box-shadow: 0 2px 6px rgba(58,12,163,0.5); pointer-events: none;
}
.remove-photo-btn {
  position: absolute; left: 6px; bottom: 6px;
  background: rgba(0,0,0,0.6); color: white; border: none;
  padding: 6px 8px; border-radius: 6px; font-size: 12px;
  cursor: pointer; display: none;
}
label { font-weight: 600; font-size: 13px; color: #444; margin-bottom: 6px; display: block; }
input, textarea, select {
  width: 96%; padding: 10px 12px; margin-bottom: 20px;
  border-radius: 6px; border: 1.8px solid #c2c2c2;
  font-size: 14px; color: #222; transition: border-color 0.3s ease;
  resize: none; font-family: inherit;
}
input:focus, textarea:focus, select:focus {
  outline: none; border-color: #3a0ca3; box-shadow: 0 0 5px #3a0ca3aa;
}
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="back" onclick="history.back()">← Edit profile</div>
    <div class="actions">
      <button class="clear-btn" id="clearBtn">Clear</button>
      <button id="saveBtn" class="save-btn">Save</button>
    </div>
  </div>
  <?php if($success): ?><div style="color:green;text-align:center;margin-bottom:10px;">Profile updated successfully!</div><?php endif; ?>
  <div class="profile-photo-wrapper" id="photoWrapper">
    <img id="profilePhotoPreview" src="<?= htmlspecialchars($user['profile_pic'] ?: 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100'); ?>" alt="Profile photo">
    <div class="camera-icon">📷</div>
    <button id="removePhotoBtn" class="remove-photo-btn" type="button">Remove</button>
  </div>
  <label class="change-photo-label">Change profile photo</label>

  <form id="editProfileForm" method="POST" enctype="multipart/form-data">
    <input type="hidden" id="remove_photo" name="remove_photo" value="0">
    <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" style="display:none">
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
    <label>Phone</label>
    <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
    <label>Bio</label>
    <textarea name="bio"><?= htmlspecialchars($user['interests'] ?? '') ?></textarea>
    <label>Gender</label>
    <select name="gender">
      <option value="">Select gender</option>
      <option value="Male" <?= ($user['gender']??'')==='Male'?'selected':'' ?>>Male</option>
      <option value="Female" <?= ($user['gender']??'')==='Female'?'selected':'' ?>>Female</option>
      <option value="Other" <?= ($user['gender']??'')==='Other'?'selected':'' ?>>Other</option>
    </select>
  </form>
</div>

<script>
const form=document.getElementById('editProfileForm');
const saveBtn=document.getElementById('saveBtn');
const clearBtn=document.getElementById('clearBtn');
const photoInput=document.getElementById('profilePhotoInput');
const preview=document.getElementById('profilePhotoPreview');
const removeBtn=document.getElementById('removePhotoBtn');
const wrapper=document.getElementById('photoWrapper');
const removeHidden=document.getElementById('remove_photo');

wrapper.onclick=()=>photoInput.click();
photoInput.onchange=(e)=>{
  const f=e.target.files[0]; if(!f)return;
  const r=new FileReader(); r.onload=(ev)=>{preview.src=ev.target.result;removeBtn.style.display='block';removeHidden.value='0';};
  r.readAsDataURL(f);
};
removeBtn.onclick=(e)=>{
  e.stopPropagation();
  preview.src='https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=100';
  photoInput.value=''; removeHidden.value='1'; removeBtn.style.display='none';
};
saveBtn.onclick=()=>form.submit();
clearBtn.onclick=()=>{if(confirm('Clear all fields?'))form.reset();};
</script>
</body>
</html>
