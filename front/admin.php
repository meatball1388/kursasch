<?php
session_start();
if (!isset($_SESSION['user']) || !$_SESSION['user']['logged_in'] || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403); echo "<h1>Доступ запрещён</h1><a href='index.php'>На главную</a>"; exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Панель администратора — BRONIC.RU</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/style.css">
<style>
.stat-card{border-radius:16px;border:none;transition:.2s;background:#fff;}
.stat-card:hover{transform:translateY(-3px);box-shadow:0 8px 28px rgba(0,0,0,.12)!important;}
.stat-icon{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;}
.admin-nav .nav-link{border-radius:10px;font-weight:500;color:#6b7280;padding:10px 16px;transition:.15s;}
.admin-nav .nav-link.active,.admin-nav .nav-link:hover{background:linear-gradient(135deg,#fe496a,#ff8c42);color:#fff!important;}
.table th{font-weight:600;font-size:.8rem;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af;border-bottom:1px solid #f3f4f6;}
.table td{vertical-align:middle;font-size:.875rem;border-bottom:1px solid #f9fafb;}
.table tr:hover td{background:#fafafa;}
.action-btn{width:30px;height:30px;border-radius:8px;border:none;display:inline-flex;align-items:center;justify-content:center;font-size:.85rem;transition:.15s;cursor:pointer;}
.action-btn:hover{transform:scale(1.12);}
</style>
<link rel="icon" href="../img/bronic.png" type="image/png">
</head>
<body class="bg-light">
<?php include 'inc/_nav.php'; ?>

<div class="container py-4">
  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-bold mb-1"><i class="bi bi-shield-lock me-2" style="color:#fe496a;"></i>Панель администратора</h4>
      <p class="text-muted mb-0 small">Управление объектами, пользователями и бронированиями</p>
    </div>
    <span class="badge px-3 py-2 fs-6" style="background:linear-gradient(135deg,#fe496a,#ff8c42);border-radius:10px;">ADMIN</span>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
      <div class="stat-card shadow-sm p-3 d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:#dbeafe;"><i class="bi bi-people-fill text-primary"></i></div>
        <div><div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;">Пользователи</div><div class="fw-bold fs-4" id="sUsers">—</div></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card shadow-sm p-3 d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:#d1fae5;"><i class="bi bi-house-fill text-success"></i></div>
        <div><div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;">Объекты</div><div class="fw-bold fs-4" id="sResources">—</div></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card shadow-sm p-3 d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:#fef3c7;"><i class="bi bi-calendar-check-fill text-warning"></i></div>
        <div><div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;">Бронирования</div><div class="fw-bold fs-4" id="sBookings">—</div></div>
      </div>
    </div>
    <div class="col-6 col-lg-3">
      <div class="stat-card shadow-sm p-3 d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:#fce7eb;"><i class="bi bi-cash-stack" style="color:#fe496a;"></i></div>
        <div><div class="text-muted" style="font-size:.75rem;text-transform:uppercase;letter-spacing:.5px;">Выручка</div><div class="fw-bold" id="sRevenue" style="font-size:1.2rem;">—</div></div>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <!-- Sidebar -->
    <div class="col-lg-2">
      <div class="bg-white rounded-3 shadow-sm p-2">
        <nav class="admin-nav nav flex-column gap-1">
          <a class="nav-link active" href="#" id="tab-users"     onclick="switchTab('users',this)"><i class="bi bi-people me-2"></i>Пользователи</a>
          <a class="nav-link"        href="#" id="tab-resources" onclick="switchTab('resources',this)"><i class="bi bi-house me-2"></i>Объекты</a>
          <a class="nav-link"        href="#" id="tab-bookings"  onclick="switchTab('bookings',this)"><i class="bi bi-calendar me-2"></i>Брони</a>
          <a class="nav-link"        href="#" id="tab-add"       onclick="switchTab('add',this)"><i class="bi bi-plus-circle me-2"></i>Добавить</a>
        </nav>
      </div>
    </div>

    <!-- Content -->
    <div class="col-lg-10">
      <div class="bg-white rounded-3 shadow-sm p-4">

        <!-- USERS -->
        <div id="pane-users">
          <div class="d-flex gap-2 mb-3">
            <input class="form-control" id="searchUsers" placeholder="🔍 Поиск по email или имени..." style="max-width:320px;">
          </div>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>ID</th><th>Имя</th><th>Email</th><th>Роль</th><th>Зарегистрирован</th><th></th></tr></thead>
              <tbody id="tbUsers"><tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-danger spinner-border-sm"></div></td></tr></tbody>
            </table>
          </div>
        </div>

        <!-- RESOURCES -->
        <div id="pane-resources" class="d-none">
          <div class="d-flex gap-2 mb-3 flex-wrap">
            <input class="form-control" id="searchResources" placeholder="🔍 Поиск по названию..." style="max-width:260px;">
            <select class="form-select" id="filterType" style="width:auto;">
              <option value="">Все типы</option>
              <option value="appartment">Квартира</option>
              <option value="dacha">Дача</option>
              <option value="room">Комната</option>
              <option value="cottedzh">Коттедж</option>
            </select>
            <select class="form-select" id="filterActive" style="width:auto;">
              <option value="">Любой статус</option>
              <option value="true">Активен</option>
              <option value="false">Отключён</option>
            </select>
          </div>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>ID</th><th>Фото</th><th>Название</th><th>Тип</th><th>Цена</th><th>Город</th><th>Статус</th><th></th></tr></thead>
              <tbody id="tbResources"><tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-danger spinner-border-sm"></div></td></tr></tbody>
            </table>
          </div>
        </div>

        <!-- BOOKINGS -->
        <div id="pane-bookings" class="d-none">
          <div class="d-flex gap-2 mb-3 flex-wrap">
            <input class="form-control" id="searchBookings" placeholder="🔍 Поиск..." style="max-width:260px;">
            <select class="form-select" id="filterStatus" style="width:auto;">
              <option value="">Все статусы</option>
              <option value="CREATED">CREATED</option>
              <option value="CONFIRMED">CONFIRMED</option>
              <option value="PAID">PAID</option>
              <option value="CANCELLED">CANCELLED</option>
            </select>
          </div>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>#</th><th>Клиент</th><th>Объект</th><th>Заезд</th><th>Выезд</th><th>Сумма</th><th>Статус</th><th></th></tr></thead>
              <tbody id="tbBookings"><tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-danger spinner-border-sm"></div></td></tr></tbody>
            </table>
          </div>
        </div>

        <!-- ADD RESOURCE -->
        <div id="pane-add" class="d-none">
          <h6 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2" style="color:#fe496a;"></i>Добавить новый объект</h6>
          <div id="addAlert"></div>
          <form id="addForm">
            <div class="row g-3">
              <div class="col-md-8">
                <label class="form-label fw-semibold small">Название</label>
                <input type="text" class="form-control" id="rName" placeholder="Апартаменты у парка" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold small">Тип</label>
                <select class="form-select" id="rType">
                  <option value="appartment">Квартира</option>
                  <option value="dacha">Дача</option>
                  <option value="room">Комната</option>
                  <option value="cottedzh">Коттедж</option>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Адрес</label>
                <input type="text" class="form-control" id="rAddress" placeholder="Москва, ул. Пушкина, д. 1" required>
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Город / Регион</label>
                <input type="text" class="form-control" id="rLocation" placeholder="Москва" required>
              </div>
              <div class="col-md-4">
                <label class="form-label fw-semibold small">Цена за сутки (₽)</label>
                <input type="number" class="form-control" id="rPrice" placeholder="2500" min="0" required>
              </div>
              <div class="col-md-8">
                <label class="form-label fw-semibold small">URL фотографии (необязательно)</label>
                <input type="url" class="form-control" id="rImage" placeholder="https://...">
              </div>
              <div class="col-12">
                <label class="form-label fw-semibold small">Описание</label>
                <textarea class="form-control" id="rDesc" rows="3" placeholder="Уютная квартира в центре..."></textarea>
              </div>
              <div class="col-12">
                <button type="submit" class="btn px-5" id="addBtn"
                        style="background:linear-gradient(135deg,#fe496a,#ff8c42);color:#fff;border:none;border-radius:12px;padding:11px 28px;font-weight:700;">
                  <i class="bi bi-plus-circle me-2"></i>Добавить объект
                </button>
              </div>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog"><div class="modal-content border-0 shadow">
    <div class="modal-header border-0 pb-0">
      <h6 class="modal-title fw-bold" id="editModalTitle">Редактирование</h6>
      <button class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body" id="editModalBody"></div>
    <div class="modal-footer border-0 pt-0">
      <button class="btn btn-light rounded-3" data-bs-dismiss="modal">Отмена</button>
      <button class="btn rounded-3 text-white" id="saveEditBtn" style="background:linear-gradient(135deg,#fe496a,#ff8c42);border:none;font-weight:600;">Сохранить</button>
    </div>
  </div></div>
</div>

<?php include 'inc/_footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const API = 'http://' + (window.location.hostname || 'localhost') + ':8000';
let cache = {users:[],resources:[],bookings:[]};
let currentTable = 'users', currentEditId = null;
const typeNames = {appartment:'Квартира',apartment:'Квартира',dacha:'Дача',room:'Комната',cottedzh:'Коттедж'};
const statusColors = {CREATED:'warning',CONFIRMED:'success',PAID:'primary',CANCELLED:'secondary'};

// === STATS ===
function loadStats(){
  $.getJSON(API+'/stats', function(s){
    $('#sUsers').text(s.users);
    $('#sResources').text(s.resources);
    $('#sBookings').text(s.bookings);
    $('#sRevenue').text(Number(s.revenue||0).toLocaleString('ru-RU')+' ₽');
  });
}

// === TABS ===
function switchTab(tab, el){
  event.preventDefault();
  $('.admin-nav .nav-link').removeClass('active');
  $(el).addClass('active');
  $('[id^="pane-"]').addClass('d-none');
  $('#pane-'+tab).removeClass('d-none');
  currentTable = tab;
  if(tab !== 'add') loadTable(tab);
}

// === LOAD TABLE ===
function loadTable(tab){
  const tbody = $('#tb'+cap(tab));
  tbody.html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-danger spinner-border-sm"></div></td></tr>');
  $.ajax({
    url:API+'/admin_api', method:'POST', contentType:'application/json',
    data:JSON.stringify({action:'get_all', table:tab}),
    success(r){
      cache[tab] = r.results||[];
      renderTable(tab, cache[tab]);
    },
    error(){ tbody.html('<tr><td colspan="9" class="text-center text-danger py-3">Ошибка загрузки</td></tr>'); }
  });
}

function cap(s){ return s.charAt(0).toUpperCase()+s.slice(1); }

function renderTable(tab, data){
  const tbody = $('#tb'+cap(tab));
  tbody.empty();
  if(!data.length){ tbody.html('<tr><td colspan="9" class="text-center text-muted py-5"><i class="bi bi-inbox fs-2 d-block mb-2 opacity-25"></i>Нет данных</td></tr>'); return; }

  data.forEach(item => {
    let row = '';
    if(tab==='users'){
      const roleBadge = item.role==='admin'
        ? '<span class="badge rounded-2" style="background:#fce7eb;color:#be123c;">admin</span>'
        : '<span class="badge rounded-2 bg-light text-muted">user</span>';
      const dt = item.created_at ? item.created_at.split('T')[0] : '—';
      row = `<tr>
        <td class="text-muted" style="font-size:.75rem;">#${item.id}</td>
        <td><div class="fw-semibold">${esc(item.name||'')} ${esc(item.surname||'')}</div></td>
        <td>${esc(item.email)}</td>
        <td>${roleBadge}</td>
        <td class="text-muted small">${dt}</td>
        <td><div class="d-flex gap-1">
          <button class="action-btn bg-light text-primary edit-btn" data-id="${item.id}" title="Изменить"><i class="bi bi-pencil"></i></button>
          <button class="action-btn bg-light text-danger delete-btn" data-id="${item.id}" title="Удалить"><i class="bi bi-trash3"></i></button>
        </div></td></tr>`;
    } else if(tab==='resources'){
      const badge = item.is_active
        ? '<span class="badge rounded-2" style="background:#d1fae5;color:#065f46;">Активен</span>'
        : '<span class="badge rounded-2 bg-light text-muted">Отключён</span>';
      const img = item.image_url ? `<img src="${esc(item.image_url)}" style="width:48px;height:36px;object-fit:cover;border-radius:6px;" onerror="this.style.display='none'">` : '<span class="text-muted small">—</span>';
      row = `<tr>
        <td class="text-muted" style="font-size:.75rem;">#${item.id}</td>
        <td>${img}</td>
        <td><div class="fw-semibold">${esc(item.name)}</div></td>
        <td class="text-muted small">${typeNames[item.type]||item.type}</td>
        <td class="fw-semibold">${Number(item.base_price).toLocaleString('ru-RU')} ₽</td>
        <td class="text-muted small">${esc(item.location||'—')}</td>
        <td>${badge}</td>
        <td><div class="d-flex gap-1">
          <button class="action-btn bg-light text-primary edit-btn" data-id="${item.id}" title="Изменить"><i class="bi bi-pencil"></i></button>
          <button class="action-btn bg-light text-danger delete-btn" data-id="${item.id}" title="Удалить"><i class="bi bi-trash3"></i></button>
        </div></td></tr>`;
    } else if(tab==='bookings'){
      const bc = statusColors[item.status]||'secondary';
      const from = (item.start_time||'').split('T')[0]||'—';
      const to   = (item.end_time  ||'').split('T')[0]||'—';
      row = `<tr>
        <td class="font-monospace text-muted" style="font-size:.75rem;">#${item.id}</td>
        <td><div class="fw-semibold">${esc(item.user_name||'ID:'+item.user_id)}</div><div class="text-muted" style="font-size:.75rem;">${esc(item.user_email||'')}</div></td>
        <td class="small">${esc(item.resource_name||'ID:'+item.resource_id)}</td>
        <td class="small text-muted">${from}</td>
        <td class="small text-muted">${to}</td>
        <td class="fw-semibold">${Number(item.price||0).toLocaleString('ru-RU')} ₽</td>
        <td><span class="badge rounded-2 bg-${bc}">${item.status}</span></td>
        <td><div class="d-flex gap-1">
          <button class="action-btn bg-light text-primary edit-btn" data-id="${item.id}" title="Изменить"><i class="bi bi-pencil"></i></button>
          <button class="action-btn bg-light text-danger delete-btn" data-id="${item.id}" title="Удалить"><i class="bi bi-trash3"></i></button>
        </div></td></tr>`;
    }
    tbody.append(row);
  });
}

function esc(s){ return $('<div>').text(s||'').html(); }

// === LIVE SEARCH / FILTER ===
$('#searchUsers').on('input', function(){
  const q=this.value.toLowerCase();
  renderTable('users', cache.users.filter(i=>(i.email||'').toLowerCase().includes(q)||(i.name||'').toLowerCase().includes(q)));
});
$('#searchResources, #filterType, #filterActive').on('input change', function(){
  const q=$('#searchResources').val().toLowerCase();
  const t=$('#filterType').val();
  const a=$('#filterActive').val();
  renderTable('resources', cache.resources.filter(i=>
    (!q||(i.name||'').toLowerCase().includes(q))&&
    (!t||i.type===t)&&
    (a===''||String(i.is_active)===a)
  ));
});
$('#searchBookings, #filterStatus').on('input change', function(){
  const q=$('#searchBookings').val().toLowerCase();
  const s=$('#filterStatus').val();
  renderTable('bookings', cache.bookings.filter(i=>
    (!s||i.status===s)&&
    (!q||(i.user_name||i.user_email||'').toLowerCase().includes(q)||(i.resource_name||'').toLowerCase().includes(q))
  ));
});

// === DELETE ===
$(document).on('click', '.delete-btn', function(){
  if(!confirm('Удалить запись #'+$(this).data('id')+'?')) return;
  const id=$(this).data('id');
  $.ajax({url:API+'/admin_api', method:'POST', contentType:'application/json',
    data:JSON.stringify({action:'delete', table:currentTable, id}),
    success(r){ if(r.success){loadTable(currentTable);loadStats();}else alert(r.error); }
  });
});

// === EDIT ===
$(document).on('click', '.edit-btn', function(){
  currentEditId=$(this).data('id');
  const item=(cache[currentTable]||[]).find(i=>i.id==currentEditId)||{};
  let html='', title='';
  if(currentTable==='users'){
    title='Редактировать пользователя #'+item.id;
    html=`<div class="mb-3"><label class="form-label fw-semibold small">Роль</label>
      <select class="form-select" id="editRole">
        <option value="user" ${item.role==='user'?'selected':''}>user</option>
        <option value="admin" ${item.role==='admin'?'selected':''}>admin</option>
      </select></div>`;
  } else if(currentTable==='resources'){
    title='Редактировать объект #'+item.id;
    html=`
      <div class="mb-3"><label class="form-label fw-semibold small">Название</label><input class="form-control" id="editName" value="${esc(item.name||'')}"></div>
      <div class="mb-3"><label class="form-label fw-semibold small">Цена (₽)</label><input type="number" class="form-control" id="editPrice" value="${item.base_price||0}"></div>
      <div class="mb-3"><label class="form-label fw-semibold small">URL фото</label><input class="form-control" id="editImage" value="${esc(item.image_url||'')}"></div>
      <div class="mb-3"><label class="form-label fw-semibold small">Статус</label>
        <select class="form-select" id="editActive">
          <option value="true" ${item.is_active?'selected':''}>Активен</option>
          <option value="false" ${!item.is_active?'selected':''}>Отключён</option>
        </select></div>`;
  } else if(currentTable==='bookings'){
    title='Редактировать бронь #'+item.id;
    html=`<div class="mb-3"><label class="form-label fw-semibold small">Статус</label>
      <select class="form-select" id="editStatus">
        ${['CREATED','CONFIRMED','PAID','CANCELLED'].map(s=>`<option value="${s}" ${item.status===s?'selected':''}>${s}</option>`).join('')}
      </select></div>`;
  }
  $('#editModalTitle').text(title);
  $('#editModalBody').html(html);
  new bootstrap.Modal(document.getElementById('editModal')).show();
});

$('#saveEditBtn').on('click', function(){
  let fields={};
  if(currentTable==='users') fields={role:$('#editRole').val()};
  else if(currentTable==='resources') fields={name:$('#editName').val(),base_price:parseFloat($('#editPrice').val()),image_url:$('#editImage').val(),is_active:$('#editActive').val()==='true'};
  else if(currentTable==='bookings') fields={status:$('#editStatus').val()};
  $.ajax({url:API+'/admin_api', method:'POST', contentType:'application/json',
    data:JSON.stringify({action:'update', table:currentTable, id:currentEditId, fields}),
    success(r){
      if(r.success){bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();loadTable(currentTable);loadStats();}
      else alert(r.error);
    }
  });
});

// === ADD RESOURCE ===
$('#addForm').on('submit', function(e){
  e.preventDefault();
  const btn=$('#addBtn');
  btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-2"></span>Добавляем...');
  $.ajax({url:API+'/resources', method:'POST', contentType:'application/json',
    data:JSON.stringify({name:$('#rName').val(), type:$('#rType').val(),
      description:$('#rDesc').val(), base_price:parseFloat($('#rPrice').val()),
      address:$('#rAddress').val(), location:$('#rLocation').val(),
      image_url:$('#rImage').val()||null, is_active:true}),
    success(r){
      if(r.id){
        $('#addAlert').html('<div class="alert alert-success border-0 rounded-3 mb-3"><i class="bi bi-check-circle me-2"></i>Объект добавлен с ID #'+r.id+'</div>');
        $('#addForm')[0].reset();
        loadStats();
      } else {
        $('#addAlert').html('<div class="alert alert-danger border-0 rounded-3 mb-3">Ошибка: '+(r.error||'неизвестная')+'</div>');
      }
      btn.prop('disabled',false).html('<i class="bi bi-plus-circle me-2"></i>Добавить объект');
    },
    error(){
      $('#addAlert').html('<div class="alert alert-danger border-0 rounded-3 mb-3">Ошибка сервера</div>');
      btn.prop('disabled',false).html('<i class="bi bi-plus-circle me-2"></i>Добавить объект');
    }
  });
});

// Init
loadStats();
loadTable('users');
</script>
</body>
</html>
