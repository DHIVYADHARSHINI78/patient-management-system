<?php
include '../config/db.php';
include '../includes/header.php';

$name = $email = $phone = $age = $gender = $diagnosis = $doctor_id = "";
$errors = [];


$doctors = $conn->query("SELECT id, doctor_name FROM doctors ORDER BY doctor_name ");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['patient_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $phone     = trim($_POST['phone'] ?? '');
    $age       = trim($_POST['age'] ?? '');
    $gender    = trim($_POST['gender'] ?? '');
    $diagnosis = trim($_POST['diagnosis'] ?? '');
    $doctor_id = $_POST['doctor_id'] ?: NULL;


    if ($name === '')      $errors[] = "Name is missing";
    if ($email === '')     $errors[] = "Email is missing";
    if ($phone === '')     $errors[] = "Phone number is missing";
    if ($age === '')       $errors[] = "Age is missing";
    if ($gender === '')    $errors[] = "Gender is missing";
    if ($diagnosis === '') $errors[] = "Diagnosis is missing";

    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if ($phone && !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Phone number must be 10 digits";
    if ($age && (!is_numeric($age) || $age < 1 || $age > 90)) $errors[] = "Age must be between 1 and 90";

    if ($email) {
        $stmt = $conn->prepare("SELECT id FROM patients WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Email already exists";
    }
    if ($phone) {
        $stmt = $conn->prepare("SELECT id FROM patients WHERE phone=?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $errors[] = "Phone number already exists";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO patients (patient_name, email, phone, age, gender, diagnosis, doctor_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssissi", $name, $email, $phone, $age, $gender, $diagnosis, $doctor_id);

        if ($stmt->execute()) {
            header("Location: list.php?success=Patient added successfully");
            exit;
        } else {
            $errors[] = "Database error: ".$conn->error;
        }
    }
}
?>

<div class="card p-4">
    <h2>Add New Patient</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <div class="mb-3"><label>Patient Name</label><input type="text" name="patient_name" class="form-control" value="<?= htmlspecialchars($name) ?>"></div>
        <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>"></div>
        <div class="mb-3"><label>Phone</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>"></div>
        <div class="mb-3"><label>Age</label><input type="number" name="age" class="form-control" value="<?= htmlspecialchars($age) ?>"></div>
        <div class="mb-3">
            <label>Gender</label>
            <select name="gender" class="form-control">
                <option value="">-- Select Gender --</option>
                <option value="Male" <?= $gender=='Male'?'selected':'' ?>>Male</option>
                <option value="Female" <?= $gender=='Female'?'selected':'' ?>>Female</option>
                <option value="Other" <?= $gender=='Other'?'selected':'' ?>>Other</option>
            </select>
        </div>
        <div class="mb-3"><label>Diagnosis</label><input type="text" name="diagnosis" class="form-control" value="<?= htmlspecialchars($diagnosis) ?>"></div>
        <div class="mb-3">
            <label>Assign Doctor</label>
            <select name="doctor_id" class="form-control">
                <option value="">-- Select Doctor --</option>
                <?php while($doc = $doctors->fetch_assoc()): ?>
                    <option value="<?= $doc['id'] ?>" <?= ($doctor_id==$doc['id'])?'selected':'' ?>>
                        <?= htmlspecialchars($doc['doctor_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Add Patient</button>
        <a href="list.php" class="btn btn-secondary">Back to List</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
