<?php
include '../config/db.php';
include '../includes/header.php';

$limit = 5;
$page  = $_GET['page'] ?? 1;
$start = ($page - 1) * $limit;

$search    = $_GET['search'] ?? '';
$order     = $_GET['order'] ?? 'id';
$sort      = $_GET['sort'] ?? 'ASC';
$ageFilter = $_GET['age_filter'] ?? '';

$allowedOrder = ['id','patient_name','age'];
$order = in_array($order, $allowedOrder) ? $order : 'id';
$sort  = strtoupper($sort) === 'DESC' ? 'DESC' : 'ASC';

$likeSearch = "%$search%";


$sqlCount = "SELECT COUNT(*) as count FROM patients WHERE (patient_name LIKE ? OR diagnosis LIKE ?)";
if ($ageFilter == '40') $sqlCount .= " AND age > 40";

$stmt = $conn->prepare($sqlCount);
$stmt->bind_param("ss", $likeSearch, $likeSearch);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['count'];
$pages = ceil($total / $limit);


$sql = "SELECT p.*, d.doctor_name, d.specialization
        FROM patients p
        LEFT JOIN doctors d ON p.doctor_id = d.id
        WHERE (p.patient_name LIKE ? OR p.diagnosis LIKE ?)";
if ($ageFilter == '40') $sql .= " AND p.age > 40";
$sql .= " ORDER BY $order $sort LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $likeSearch, $likeSearch, $start, $limit);
$stmt->execute();
$result = $stmt->get_result();

$success = $_GET['success'] ?? '';
?>

<div class="card p-4">
    <h2>
        Patient List
        <a href="create.php" class="btn btn-success float-end">Add New</a>
    </h2>

    <?php if($success): ?>
        <div id="success-alert" class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if(alert) {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.style.display='none', 1000);
                }
            }, 1000);
        </script>
    <?php endif; ?>

  
    <form class="mb-3 d-flex gap-2" method="get">
        <input type="text" name="search" id="search" class="form-control" placeholder="Search by name or diagnosis" value=" ">
       
        <button type="submit" class="btn btn-primary">search</button>
    </form>

    <div class="mb-2">
  <div class="btn-group">
    <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
      Sort Age
    </button>
    <ul class="dropdown-menu">
      <li>
        <a class="dropdown-item" href="?order=age&sort=ASC&search=<?= urlencode($search) ?>&age_filter=<?= urlencode($ageFilter) ?>">ASC</a>
      </li>
      <li>
        <a class="dropdown-item" href="?order=age&sort=DESC&search=<?= urlencode($search) ?>&age_filter=<?= urlencode($ageFilter) ?>">DESC</a>
      </li>
    </ul>
  </div>
</div>


    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th><a href="?order=patient_name&sort=<?= $sort=='ASC'?'DESC':'ASC' ?>&search=<?= urlencode($search) ?>&age_filter=<?= urlencode($ageFilter) ?>">Name</a></th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th><a href="?order=age&sort=<?= $sort=='ASC'?'DESC':'ASC' ?>&search=<?= urlencode($search) ?>&age_filter=<?= urlencode($ageFilter) ?>">Age</a></th>
                    <th>Gender</th>
                    <th>Diagnosis</th>
                    <th>Doctor Name</th>
                    <th>Specialization</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['patient_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= $row['age'] ?></td>
                            <td><?= $row['gender'] ?></td>
                            <td><?= htmlspecialchars($row['diagnosis']) ?></td>
                            <td><?= htmlspecialchars($row['doctor_name'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['specialization'] ?? '-') ?></td>
                            <td>
                                <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this patient?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="text-center">No records found</td></tr>
                <?php endif; ?>
            </tbody>
            <script>
             window.addEventListener("pageshow", function () { const input = document.getElementById("search"); if (input) { input.value = ""; } }); </script>
        </table>
    </div>

    <nav>
        <ul class="pagination">
            <?php if($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&order=<?= $order ?>&sort=<?= $sort ?>&age_filter=<?= urlencode($ageFilter) ?>">Previous</a>
                </li>
            <?php endif; ?>
            <?php for($i=1; $i<=$pages; $i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&order=<?= $order ?>&sort=<?= $sort ?>&age_filter=<?= urlencode($ageFilter) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            <?php if($page < $pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&order=<?= $order ?>&sort=<?= $sort ?>&age_filter=<?= urlencode($ageFilter) ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<?php include '../includes/footer.php'; ?>
