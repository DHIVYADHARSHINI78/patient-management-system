<?php
include '../config/db.php';
include '../includes/header.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM patients WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if(!$patient) {
    echo "<div class='alert alert-danger'>Patient not found!</div>";
    exit;
}


$doctors = $conn->query("SELECT id, doctor_name FROM doctors ORDER BY doctor_name ASC");

$error = "";

if ($_SERVER['REQUEST_METHOD']=='POST') {
    $name      = $_POST['patient_name'];
    $email     = $_POST['email'];
    $phone     = $_POST['phone'];
    $age       = $_POST['age'];
    $gender    = $_POST['gender'];
    $diagnosis = $_POST['diagnosis'];
    $doctor_id = $_POST['doctor_id'] ?: NULL;

   
    $check = $conn->prepare("SELECT id FROM patients WHERE email=? AND id<>?");
    $check->bind_param("si", $email, $id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        $stmt = $conn->prepare("
            UPDATE patients
            SET patient_name=?, email=?, phone=?, age=?, gender=?, diagnosis=?, doctor_id=?
            WHERE id=?
        ");
        $stmt->bind_param("sssissii", $name, $email, $phone, $age, $gender, $diagnosis, $doctor_id, $id);
        if($stmt->execute()) {
            header("Location: list.php?success=Patient updated successfully!");
            exit;
        } else {
            $error = "Error: ".$conn->error;
        }
    }
}
?>

<div class="card p-4">
    <h2>Edit Patient</h2>
    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="post">
        <div class="mb-3"><label>Patient Name</label><input type="text" name="patient_name" class="form-control" value="<?= htmlspecialchars($patient['patient_name']) ?>" required></div>
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($patient['email']) ?>" required></div>
        <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($patient['phone']) ?>" required></div>
        <div class="mb-3"><label>Age</label><input type="number" name="age" class="form-control" value="<?= $patient['age'] ?>" required></div>
        <div class="mb-3">
            <label>Gender</label>
            <select name="gender" class="form-control" required>
                <option value="Male" <?= $patient['gender']=='Male'?'selected':'' ?>>Male</option>
                <option value="Female" <?= $patient['gender']=='Female'?'selected':'' ?>>Female</option>
                <option value="Other" <?= $patient['gender']=='Other'?'selected':'' ?>>Other</option>
            </select>
        </div>
        <div class="mb-3"><label>Diagnosis</label><input type="text" name="diagnosis" class="form-control" value="<?= htmlspecialchars($patient['diagnosis']) ?>" required></div>
        <div class="mb-3">
            <label>Assign Doctor</label>
            <select name="doctor_id" class="form-control">
                <option value="">-- Select Doctor --</option>
                <?php while($doc = $doctors->fetch_assoc()): ?>
                    <option value="<?= $doc['id'] ?>" <?= ($patient['doctor_id']==$doc['id'])?'selected':'' ?>>
                        <?= htmlspecialchars($doc['doctor_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Patient</button>
        <a href="list.php" class="btn btn-secondary">Back to List</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
