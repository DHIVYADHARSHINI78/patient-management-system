<?php
include '../config/db.php';
include '../includes/header.php';


$limit = 5;
$page = $_GET['page'] ?? 1;
$start = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$order  = $_GET['order'] ?? 'id';
$sort   = $_GET['sort'] ?? 'ASC';


$stmt = $conn->prepare("SELECT COUNT(*) as count FROM patients WHERE patient_name LIKE ? OR diagnosis LIKE ?");
$likeSearch = "%$search%";
$stmt->bind_param("ss", $likeSearch, $likeSearch);
$stmt->execute();
$total = $stmt->get_result()->fetch_assoc()['count'];
$pages = ceil($total/$limit);


$stmt = $conn->prepare("SELECT * FROM patients WHERE patient_name LIKE ? OR diagnosis LIKE ? ORDER BY $order $sort LIMIT ?, ?");
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
        <div id="successMsg" class="alert alert-success">
            <?= htmlspecialchars($success) ?>
        </div>

        <script>
       
            setTimeout(() => {
                const msg = document.getElementById('successMsg');
                if(msg){
                    msg.style.transition = "opacity 0.5s";
                    msg.style.opacity = 0;
                    setTimeout(() => msg.remove(), 500);
                }
            }, 5000);
        </script>
    <?php endif; ?>

   
<form class="mb-3" method="get">
    <input type="text" name="search" id="searchInput" placeholder="Search by name or diagnosis" class="form-control" value="">
    <button type="submit" class="btn btn-primary mt-2">Search</button>
</form>


<div class="table-responsive">
    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th><a href="?order=patient_name&sort=<?= $sort=='ASC'?'DESC':'ASC' ?>&search=<?= urlencode($search) ?>">Name</a></th>
                <th>Email</th>
                <th>Phone</th>
                <th><a href="?order=age&sort=<?= $sort=='ASC'?'DESC':'ASC' ?>&search=<?= urlencode($search) ?>">Age</a></th>
                <th>Gender</th>
                <th>Diagnosis</th>
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
                        <td>
                            <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this patient?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center">No records found for "<?= htmlspecialchars($search) ?>"</td>
                </tr>
            <?php endif; ?>
        </tbody>
     <script>
    window.addEventListener("pageshow", function () {
        const input = document.getElementById("searchInput");
        if (input) {
            input.value = "";
        }
    });
</script>


    </table>
            </div>

   
    <nav>
        <ul class="pagination">
            <?php if($page > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>&order=<?= $order ?>&sort=<?= $sort ?>">Previous</a>
                </li>
            <?php endif; ?>

            <?php for($i=1; $i<=$pages; $i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&order=<?= $order ?>&sort=<?= $sort ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>

            <?php if($page < $pages): ?>
                <li class="page-item">
                    <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>&order=<?= $order ?>&sort=<?= $sort ?>">Next</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>


<?php include '../includes/footer.php'; ?>
