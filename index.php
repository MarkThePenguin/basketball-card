<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hoops Hub | Player Database</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
<header>
    <h1>🏀 HOOPS HUB</h1>

    <form method="GET" class="search-box">
        <input type="text" name="search" placeholder="Search..."
        value="<?php echo $_GET['search'] ?? ''; ?>">
    </form>

    <a href="admin.php" class="btn">Admin Panel</a>
</header>

<div class="roster-grid">
<?php
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$res = $conn->query("
SELECT * FROM players 
WHERE first_name LIKE '%$search%' 
OR last_name LIKE '%$search%' 
OR team LIKE '%$search%'
ORDER BY id DESC
");

while($row = $res->fetch_assoc()): ?>
    
<div class="player-card"
onclick='openModal(<?php echo json_encode($row); ?>)'>

    <div class="card-body">

        <!-- CORNER NUMBER -->
        <div class="corner-number">
            #<?php echo $row['jersey_number']; ?>
        </div>

        <!-- IMAGE -->
        <img src="<?php echo $row['image_path']; ?>" class="player-img">

        <!-- BOTTOM BAR -->
        <div class="bottom-info">
            <div class="player-name">
                <?php echo $row['first_name'] . " " . $row['last_name']; ?>
            </div>
            <div class="player-team">
                <?php echo $row['team']; ?>
            </div>
        </div>

    </div>
</div>

<?php endwhile; ?>
</div>
</div>

<!-- ================= MODAL (UNCHANGED) ================= -->

<div id="playerModal" class="modal-overlay" onclick="closeModal()">
<div class="modal-content" onclick="event.stopPropagation()">
<span class="close-btn" onclick="closeModal()">&times;</span>

<div class="modal-body">
<div class="modal-left">
<img id="mImg">
</div>

<div class="modal-right">
<h2 id="mName"></h2>
<div id="mPos"></div>
<div id="mVitals" class="modal-vitals"></div>

<div class="modal-section">
<strong>Alma Mater</strong>
<ul id="mAlma" class="career-bullets"></ul>
</div>

<div class="modal-section">
<strong>Career Achievements</strong>
<div id="mAch" class="ach-list"></div>
</div>

<div class="modal-section">
<strong>Professional History</strong>
<ul id="mHistory" class="career-bullets"></ul>
</div>
</div>
</div>
</div>
</div>

<script>
function openModal(p){
document.getElementById('mImg').src=p.image_path;
document.getElementById('mName').innerText=
(p.first_name||'')+' '+(p.last_name||'')+' #'+(p.jersey_number||'00');

document.getElementById('mPos').innerText=p.position||'N/A';

document.getElementById('mVitals').innerHTML=`
<div class="v-item full-row"><strong>TEAM</strong><span>${p.team||'N/A'}</span></div>
<div class="v-item"><strong>NAT</strong><span>${p.nationality||'—'}</span></div>
<div class="v-item"><strong>HT</strong><span>${p.height||'—'}</span></div>
<div class="v-item"><strong>WT</strong><span>${p.weight||'—'}</span></div>
<div class="v-item"><strong>AGE</strong><span>${p.age||'—'}</span></div>
`;

const alma=document.getElementById('mAlma');
alma.innerHTML='';
if(p.alma_mater) p.alma_mater.split(',').forEach(s=>{
if(s.trim()) alma.innerHTML+=`<li>${s.trim()}</li>`;
});

const ach=document.getElementById('mAch');
ach.innerHTML='';
if(p.achievements) p.achievements.split(',').forEach(a=>{
if(a.trim()) ach.innerHTML+=`<span class="ach-item">🏆 ${a.trim()}</span>`;
});

const hist=document.getElementById('mHistory');
hist.innerHTML='';
if(p.previous_teams) p.previous_teams.split(',').forEach(t=>{
if(t.trim()) hist.innerHTML+=`<li>${t.trim()}</li>`;
});

document.getElementById('playerModal').classList.add('active');
document.body.style.overflow='hidden';
}

function closeModal(){
document.getElementById('playerModal').classList.remove('active');
document.body.style.overflow='auto';
}
</script>
</body>
</html>