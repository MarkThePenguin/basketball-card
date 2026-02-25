<?php
include 'db.php';

$edit = null;

// 1. DELETE LOGIC
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $res = $conn->query("SELECT image_path FROM players WHERE id=$id");
    $p = $res->fetch_assoc();
    if ($p && file_exists($p['image_path'])) { unlink($p['image_path']); }
    $conn->query("DELETE FROM players WHERE id=$id");
    header("Location: admin.php");
    exit;
}

// 2. LOAD DATA FOR EDITING
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $res = $conn->query("SELECT * FROM players WHERE id=$id");
    $edit = $res->fetch_assoc();
}

// 3. ADD OR UPDATE LOGIC
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $f = $_POST['f']; $l = $_POST['l']; $p = $_POST['p']; $t = $_POST['t'];
    $j = $_POST['j']; $a = $_POST['a']; $prev = $_POST['prev'];
    $h = $_POST['h']; $w = $_POST['w']; $age = $_POST['age']; 
    $dob = $_POST['dob']; $nat = $_POST['nat']; $ach = $_POST['ach'];

    // Handle Image
    if (!empty($_FILES['img']['name'])) {
        $path = "uploads/" . time() . "_" . basename($_FILES['img']['name']);
        move_uploaded_file($_FILES['img']['tmp_name'], $path);
        // Delete old image if it exists
        if (!empty($_POST['old_img']) && file_exists($_POST['old_img'])) { unlink($_POST['old_img']); }
    } else {
        $path = $_POST['old_img']; // Keep existing
    }

    if ($id) {
        // UPDATE
        $stmt = $conn->prepare("UPDATE players SET first_name=?, last_name=?, position=?, team=?, jersey_number=?, alma_mater=?, previous_teams=?, image_path=?, height=?, weight=?, age=?, dob=?, nationality=?, achievements=? WHERE id=?");
        $stmt->bind_param("ssssisssssisssi", $f, $l, $p, $t, $j, $a, $prev, $path, $h, $w, $age, $dob, $nat, $ach, $id);
    } else {
        // INSERT
        $stmt = $conn->prepare("INSERT INTO players (first_name, last_name, position, team, jersey_number, alma_mater, previous_teams, image_path, height, weight, age, dob, nationality, achievements) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssisssssisss", $f, $l, $p, $t, $j, $a, $prev, $path, $h, $w, $age, $dob, $nat, $ach);
    }
    
    $stmt->execute();
    header("Location: admin.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel | Hoops Hub</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Specific Admin UI tweaks */
        .admin-layout { display: grid; grid-template-columns: 1fr 1.5fr; gap: 40px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 0.7rem; color: var(--primary); text-transform: uppercase; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { 
            width: 100%; background: #222; border: 1px solid #333; padding: 12px; 
            border-radius: 8px; color: white; font-family: inherit;
        }
        input:focus { border-color: var(--primary); outline: none; }
        .data-table { width: 100%; border-collapse: collapse; background: var(--card); border-radius: 15px; overflow: hidden; }
        .data-table th, .data-table td { padding: 15px; text-align: left; border-bottom: 1px solid var(--glass); }
        .data-table th { background: #222; color: var(--primary); font-size: 0.8rem; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🏀 Manage Roster</h1>
            <a href="index.php" class="btn secondary">← View Public Site</a>
        </header>

        <div class="admin-layout">
            <div class="admin-form-container">
                <form method="POST" enctype="multipart/form-data" class="player-card" style="padding:30px; cursor:default;">
                    <h3 style="margin-top:0;"><?php echo $edit ? 'Edit Player Profile' : 'Add New Player'; ?></h3>
                    <input type="hidden" name="id" value="<?php echo $edit['id'] ?? ''; ?>">
                    <input type="hidden" name="old_img" value="<?php echo $edit['image_path'] ?? ''; ?>">

                    <div style="display:flex; gap:10px;" class="form-group">
                        <div style="flex:1;">
                            <label>First Name</label>
                            <input type="text" name="f" value="<?php echo $edit['first_name'] ?? ''; ?>" required>
                        </div>
                        <div style="flex:1;">
                            <label>Last Name</label>
                            <input type="text" name="l" value="<?php echo $edit['last_name'] ?? ''; ?>" required>
                        </div>
                    </div>

                    <div style="display:flex; gap:10px;" class="form-group">
                        <div style="flex:2;">
                            <label>Current Team</label>
                            <input type="text" name="t" placeholder="e.g. LA Lakers" value="<?php echo $edit['team'] ?? ''; ?>">
                        </div>
                        <div style="flex:1;">
                            <label>Jersey #</label>
                            <input type="number" name="j" placeholder="23" value="<?php echo $edit['jersey_number'] ?? ''; ?>">
                        </div>
                    </div>

                    <div style="display:flex; gap:10px;" class="form-group">
                        <div style="flex:1;">
                            <label>Height</label>
                            <input type="text" name="h" placeholder="6'9\"" value="<?php echo $edit['height'] ?? ''; ?>">
                        </div>
                        <div style="flex:1;">
                            <label>Weight</label>
                            <input type="text" name="w" placeholder="250 lbs" value="<?php echo $edit['weight'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Nationality</label>
                        <input type="text" name="nat" placeholder="USA 🇺🇸" value="<?php echo $edit['nationality'] ?? ''; ?>">
                    </div>

                    <div class="form-group">
                        <label>Position</label>
                        <select name="p">
                            <?php 
                            $pos = ["Point Guard", "Shooting Guard", "Small Forward", "Power Forward", "Center"];
                            foreach($pos as $o) {
                                $s = (isset($edit['position']) && $edit['position'] == $o) ? 'selected' : '';
                                echo "<option value='$o' $s>$o</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Alma Mater</label>
                        <input type="text" name="a" placeholder="University Name" value="<?php echo $edit['alma_mater'] ?? ''; ?>">
                    </div>

                    <div style="display:flex; gap:10px;" class="form-group">
                        <div style="flex:1;">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" value="<?php echo $edit['dob'] ?? ''; ?>">
                        </div>
                        <div style="flex:1;">
                            <label>Age</label>
                            <input type="number" name="age" value="<?php echo $edit['age'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Career Achievements (Comma Separated)</label>
                        <textarea name="ach" placeholder="2x MVP, 4x NBA Champ"><?php echo $edit['achievements'] ?? ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Career History (Comma Separated)</label>
                        <textarea name="prev" placeholder="Heat (2010-14), Cavs (2014-18)"><?php echo $edit['previous_teams'] ?? ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Player Photo</label>
                        <input type="file" name="img" <?php echo $edit ? '' : 'required'; ?>>
                    </div>

                    <button type="submit" class="btn" style="width:100%; margin-top:10px;">
                        <?php echo $edit ? 'Update Player' : 'Add Player to Roster'; ?>
                    </button>
                    <?php if($edit): ?>
                        <a href="admin.php" style="display:block; text-align:center; margin-top:15px; color:var(--dim); text-decoration:none; font-size:0.8rem;">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="admin-list-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Img</th>
                            <th>Player</th>
                            <th>Team</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res = $conn->query("SELECT * FROM players ORDER BY id DESC");
                        while($row = $res->fetch_assoc()): ?>
                        <tr>
                            <td><img src="<?php echo $row['image_path']; ?>" width="40" height="40" style="object-fit:cover; border-radius:5px;"></td>
                            <td>
                                <strong><?php echo $row['first_name']." ".$row['last_name']; ?></strong><br>
                                <small style="color:var(--dim);">#<?php echo $row['jersey_number']; ?></small>
                            </td>
                            <td><?php echo $row['team']; ?></td>
                            <td>
                                <a href="admin.php?edit=<?php echo $row['id']; ?>" style="color:var(--primary); text-decoration:none; margin-right:10px; font-weight:bold;">Edit</a>
                                <a href="admin.php?delete=<?php echo $row['id']; ?>" style="color:#ff4444; text-decoration:none; font-weight:bold;" onclick="return confirm('Delete this player?')">Del</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>