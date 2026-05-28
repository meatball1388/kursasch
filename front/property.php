<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$propertyId = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Объект — BRONIC.RU</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="../assets/style.css">
<link rel="icon" href="../img/bronic.png" type="image/png">
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
* { font-family: 'Inter', sans-serif; }

/* ===== HERO ===== */
.hero-wrap { position:relative; height:520px; overflow:hidden; }
.hero-img { width:100%; height:100%; object-fit:cover; transition:transform 8s ease; }
.hero-wrap:hover .hero-img { transform:scale(1.04); }
.hero-overlay {
    position:absolute; inset:0;
    background:linear-gradient(to bottom, rgba(0,0,0,.15) 0%, rgba(0,0,0,.65) 100%);
}
.hero-content {
    position:absolute; bottom:0; left:0; right:0;
    padding:40px 48px;
    color:#fff;
}
.hero-content h1 { font-size:2.4rem; font-weight:800; text-shadow:0 2px 12px rgba(0,0,0,.4); margin-bottom:8px; }
.hero-badge { background:rgba(255,255,255,.2); backdrop-filter:blur(8px);
    border:1px solid rgba(255,255,255,.35); border-radius:20px;
    padding:5px 14px; font-size:.85rem; color:#fff; display:inline-flex; align-items:center; gap:6px; }
.breadcrumb-hero { 
    position:absolute; top:20px; left:48px; z-index:10;
    background:rgba(0,0,0,0.55); backdrop-filter:blur(8px);
    padding:8px 16px; border-radius:12px;
    border:1px solid rgba(255,255,255,0.15);
}
.breadcrumb-hero a { color:#fff; text-decoration:none; font-size:.95rem; font-weight:500; transition:0.2s; text-shadow: 0 1px 3px rgba(0,0,0,0.5); }
.breadcrumb-hero a:hover { color:#fe496a; }
.breadcrumb-hero span { color:rgba(255,255,255,.5); margin:0 8px; font-size:.95rem; }

/* ===== BOOKING CARD ===== */
.booking-card {
    position:sticky; top:88px;
    border-radius:20px;
    border:none;
    box-shadow:0 8px 40px rgba(0,0,0,.14);
    overflow:hidden;
}
.booking-card .card-top {
    background:linear-gradient(135deg,#fe496a,#ff8c42);
    padding:24px;
    color:#fff;
}
.booking-card .price-big { font-size:2rem; font-weight:800; }
.booking-card .card-bottom { padding:24px; }
.btn-book-main {
    background:linear-gradient(135deg,#fe496a,#ff8c42);
    border:none; border-radius:12px;
    color:#fff; font-weight:700; font-size:1.05rem;
    padding:14px 0; width:100%;
    transition:.25s; letter-spacing:.3px;
}
.btn-book-main:hover { transform:translateY(-2px); box-shadow:0 6px 24px rgba(254,73,106,.45); color:#fff; }

/* ===== STARS ===== */
.star-filled { color:#f59e0b; }
.star-empty  { color:#d1d5db; }

/* ===== AMENITY ===== */
.amenity-chip {
    display:inline-flex; align-items:center; gap:8px;
    background:#f8fafc; border:1px solid #e2e8f0;
    border-radius:10px; padding:10px 16px;
    font-size:.9rem; font-weight:500; color:#374151;
    transition:.2s;
}
.amenity-chip:hover { background:#fff5f7; border-color:#fe496a; color:#fe496a; }
.amenity-chip i { font-size:1.1rem; color:#fe496a; }

/* ===== SECTION TITLES ===== */
.section-title {
    font-size:1.3rem; font-weight:700; color:#111827;
    display:flex; align-items:center; gap:10px; margin-bottom:20px;
}
.section-title::after {
    content:''; flex:1; height:2px;
    background:linear-gradient(90deg,#fe496a22,transparent);
    border-radius:2px;
}

/* ===== REVIEWS ===== */
.review-card {
    border:1px solid #f1f5f9; border-radius:14px;
    padding:20px; background:#fff;
    transition:.2s; position:relative; overflow:hidden;
}
.review-card::before {
    content:''; position:absolute; left:0; top:0; bottom:0;
    width:4px; background:linear-gradient(180deg,#fe496a,#ff8c42);
    border-radius:4px 0 0 4px;
}
.review-card:hover { box-shadow:0 4px 20px rgba(0,0,0,.08); transform:translateY(-2px); }
.avatar-circle {
    width:44px; height:44px; border-radius:50%;
    background:linear-gradient(135deg,#fe496a,#ff8c42);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-weight:700; font-size:1rem; flex-shrink:0;
}

/* ===== RATING SUMMARY ===== */
.rating-big { font-size:4rem; font-weight:800; color:#111; line-height:1; }
.rating-bar-wrap { display:flex; align-items:center; gap:10px; margin-bottom:6px; }
.rating-bar-bg { flex:1; height:7px; background:#f1f5f9; border-radius:10px; overflow:hidden; }
.rating-bar-fill { height:100%; background:linear-gradient(90deg,#fe496a,#ff8c42); border-radius:10px; transition:width .8s ease; }

/* ===== REVIEW FORM ===== */
.review-form-wrap { background:linear-gradient(135deg,#fff5f7,#fff);
    border:1px solid #fce7eb; border-radius:16px; padding:28px; }
.star-select-btn { font-size:2rem; cursor:pointer; transition:.15s; color:#d1d5db; }
.star-select-btn.active,.star-select-btn:hover { color:#f59e0b; transform:scale(1.2); }

/* ===== ANIMATE ===== */
@keyframes fadeUp { from{opacity:0;transform:translateY(24px)} to{opacity:1;transform:none} }
.fade-up { animation:fadeUp .5s ease forwards; }
.delay-1 { animation-delay:.1s; }
.delay-2 { animation-delay:.2s; }
.delay-3 { animation-delay:.3s; }
</style>
</head>
<body style="background:#f8fafc;">
<?php include 'inc/_nav.php'; ?>

<!-- LOADING -->
<div id="pageLoader" class="text-center py-5 mt-5">
    <div class="spinner-border text-danger" style="width:3.5rem;height:3.5rem;"></div>
    <p class="mt-3 text-muted fw-medium">Загружаем объект...</p>
</div>

<!-- MAIN CONTENT -->
<div id="pageContent" style="display:none;">

    <!-- HERO -->
    <div class="hero-wrap">
        <img id="heroImg" src="" alt="" class="hero-img">
        <div class="hero-overlay"></div>
        <div class="breadcrumb-hero">
            <a href="index.php"><i class="bi bi-house-fill me-1"></i>Главная</a>
            <span>/</span>
            <a href="index.php">Объекты</a>
            <span>/</span>
            <a id="heroBreadcrumb" href="#">—</a>
        </div>
        <div class="hero-content">
            <div class="d-flex gap-2 mb-2 flex-wrap" id="heroBadges"></div>
            <h1 id="heroTitle">—</h1>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="hero-badge"><i class="bi bi-geo-alt-fill"></i><span id="heroAddress">—</span></span>
                <span class="hero-badge" id="heroRatingBadge"><i class="bi bi-star-fill text-warning"></i><span id="heroRating">—</span></span>
            </div>
        </div>
    </div>

    <!-- BODY -->
    <div class="container py-5">
        <div class="row g-5">

            <!-- LEFT -->
            <div class="col-lg-8">

                <!-- Description -->
                <div class="fade-up delay-1 mb-5">
                    <div class="section-title"><i class="bi bi-info-circle-fill text-danger"></i>Описание</div>
                    <p id="propertyDesc" class="text-muted lh-lg fs-5" style="color:#4b5563!important;"></p>
                </div>

                <!-- Amenities -->
                <div class="fade-up delay-2 mb-5">
                    <div class="section-title"><i class="bi bi-stars text-danger"></i>Удобства</div>
                    <div class="d-flex flex-wrap gap-2" id="amenitiesBlock">
                        <span class="amenity-chip"><i class="bi bi-wifi"></i>Wi-Fi</span>
                        <span class="amenity-chip"><i class="bi bi-cup-hot"></i>Кухня</span>
                        <span class="amenity-chip"><i class="bi bi-car-front"></i>Парковка</span>
                        <span class="amenity-chip"><i class="bi bi-tv"></i>Телевизор</span>
                        <span class="amenity-chip"><i class="bi bi-snow"></i>Кондиционер</span>
                        <span class="amenity-chip"><i class="bi bi-shield-check"></i>Безопасность</span>
                    </div>
                </div>

                <!-- Reviews Summary -->
                <div class="fade-up delay-3 mb-4">
                    <div class="section-title"><i class="bi bi-chat-square-heart-fill text-danger"></i>Отзывы</div>
                    <div class="row g-4 align-items-center mb-4" id="ratingsSummary">
                        <div class="col-auto text-center">
                            <div class="rating-big" id="avgRatingBig">—</div>
                            <div id="avgStars" class="my-1"></div>
                            <div class="text-muted small" id="reviewsCountLabel"></div>
                        </div>
                        <div class="col" id="ratingBars"></div>
                    </div>
                    <div id="reviewsList"></div>
                </div>

                <!-- Review Form -->
                <div class="review-form-wrap fade-up">
                    <div class="section-title" style="margin-bottom:16px;"><i class="bi bi-pencil-square text-danger"></i>Оставить отзыв</div>
                    <form id="reviewForm">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Ваше имя</label>
                            <input type="text" class="form-control form-control-lg" id="reviewName" placeholder="Иван Иванов" required style="border-radius:10px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium">Оценка</label>
                            <div class="d-flex gap-1 mb-1" id="starSelector">
                                <span class="star-select-btn" data-v="1">★</span>
                                <span class="star-select-btn" data-v="2">★</span>
                                <span class="star-select-btn" data-v="3">★</span>
                                <span class="star-select-btn" data-v="4">★</span>
                                <span class="star-select-btn" data-v="5">★</span>
                            </div>
                            <input type="hidden" id="reviewRating" value="5">
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-medium">Комментарий</label>
                            <textarea class="form-control" id="reviewComment" rows="3" placeholder="Расскажите о своём опыте..." style="border-radius:10px;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-book-main px-5" style="width:auto;padding:12px 32px!important;">
                            <i class="bi bi-send me-2"></i>Отправить отзыв
                        </button>
                    </form>
                </div>
            </div>

            <!-- RIGHT: Booking Card -->
            <div class="col-lg-4">
                <div class="booking-card card">
                    <div class="card-top">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="text-white-50 small mb-1">Цена за сутки</div>
                                <div class="price-big" id="bookingPrice">—</div>
                            </div>
                            <div class="text-end">
                                <div id="bookingRating" class="fs-5"></div>
                                <div class="text-white-50 small" id="bookingReviewsCount"></div>
                            </div>
                        </div>
                    </div>
                    <div class="card-bottom">
                        <div class="mb-3">
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-1">Заезд</label>
                                    <input type="text" class="form-control" id="checkinDate" style="border-radius:10px;" placeholder="дд.мм.гггг">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-1">Выезд</label>
                                    <input type="text" class="form-control" id="checkoutDate" style="border-radius:10px;" placeholder="дд.мм.гггг">
                                </div>
                            </div>
                        </div>
                        <div class="bg-light rounded-3 p-3 mb-3 d-none" id="priceBreakdown">
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span id="pricePerNightLabel">—</span><span id="pricePerNightTotal">—</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between fw-bold">
                                <span>Итого</span><span id="priceTotal" class="text-danger">—</span>
                            </div>
                        </div>
                        <button class="btn-book-main mb-3" id="bookBtn">
                            <i class="bi bi-calendar-check me-2"></i>Забронировать
                        </button>
                        <button class="btn btn-outline-secondary w-100 btn-show-phone" style="border-radius:12px;padding:12px 0;">
                            <i class="bi bi-telephone me-2"></i>Показать телефон
                        </button>
                        <div class="text-center mt-3 text-muted small">
                            <i class="bi bi-shield-check text-success me-1"></i>Безопасное бронирование
                            <span class="mx-2">·</span>
                            <i class="bi bi-arrow-counterclockwise text-warning me-1"></i>Бесплатная отмена
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc/_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script src="main.js"></script>
<script>
const PID = <?= $propertyId ?>;
const API = 'http://' + (window.location.hostname || 'localhost') + ':8000';
let propertyData = null;

function esc(s) { return $('<div>').text(s||'').html(); }
function stars(n, size='') {
    return [1,2,3,4,5].map(i=>`<i class="bi bi-star${i<=n?'-fill star-filled':' star-empty'} ${size}"></i>`).join('');
}
function avg(reviews) {
    if (!reviews.length) return 0;
    return reviews.reduce((a,r)=>a+r.rating,0)/reviews.length;
}

function renderPage(item, reviews) {
    propertyData = item;
    const avgR = avg(reviews);
    const reviewCount = reviews.length;
    const img = item.image_url || '../img/property/metro-plus.png';
    const priceF = Number(item.base_price).toLocaleString('ru-RU');

    // Hero
    $('#heroImg').attr('src', img).attr('alt', item.name);
    $('#heroTitle').text(item.name);
    $('#heroAddress').text(item.address || item.location || 'Адрес не указан');
    $('#heroBreadcrumb').text(item.name);
    const typeNames = {apartment:'Квартира',dacha:'Дача',room:'Комната',cottedzh:'Коттедж'};
    const typeName = typeNames[item.type] || 'Недвижимость';
    $('#heroBadges').html(`
        <span class="hero-badge"><i class="bi bi-tag-fill"></i>${esc(typeName)}</span>
        <span class="hero-badge"><i class="bi bi-people-fill"></i>${item.guests || 2} гост.</span>
        <span class="hero-badge"><i class="bi bi-door-open-fill"></i>${item.bedrooms || 1} сп.</span>
        <span class="hero-badge"><i class="bi bi-aspect-ratio-fill"></i>${item.area || 45} м²</span>
        ${avgR>0?`<span class="hero-badge"><i class="bi bi-star-fill text-warning"></i>${avgR.toFixed(1)} · ${reviewCount} отзывов</span>`:''}
    `);
    $('#heroRatingBadge').toggle(avgR>0);
    $('#heroRating').text(avgR>0?`${avgR.toFixed(1)} · ${reviewCount} отзывов`:'');

    // Description
    $('#propertyDesc').text(item.description || 'Описание не указано');

    // Amenities
    const amenityMap = {
        'wifi': { icon: 'bi-wifi', label: 'Wi-Fi' },
        'kitchen': { icon: 'bi-cup-hot', label: 'Кухня' },
        'parking': { icon: 'bi-car-front', label: 'Парковка' },
        'tv': { icon: 'bi-tv', label: 'ТВ' },
        'washer': { icon: 'bi-lightning-charge', label: 'Стиральная машина' },
        'ac': { icon: 'bi-snow', label: 'Кондиционер' },
        'safe': { icon: 'bi-shield-check', label: 'Безопасность' }
    };
    let amens = [];
    try {
        if (item.amenities) {
            amens = typeof item.amenities === 'string' ? JSON.parse(item.amenities) : item.amenities;
        }
    } catch(e) { console.error("Parse amenities error", e); }
    let amenHtml = '';
    if (amens && amens.length > 0) {
        amens.forEach(key => {
            const info = amenityMap[key] || { icon: 'bi-check-circle', label: key };
            amenHtml += `<span class="amenity-chip"><i class="bi ${info.icon}"></i>${info.label}</span>`;
        });
    } else {
        amenHtml = '<p class="text-muted small">Удобства не указаны</p>';
    }
    $('#amenitiesBlock').html(amenHtml);

    // Booking card
    $('#bookingPrice').html(`${priceF} ₽ <span style="font-size:.95rem;font-weight:400;opacity:.75;">/ ночь</span>`);
    $('#bookingRating').html(avgR>0?`${stars(Math.round(avgR))} <span class="text-white-50 ms-1">${avgR.toFixed(1)}</span>`:'');
    $('#bookingReviewsCount').text(reviewCount>0?`${reviewCount} отзывов`:'');

    // Rating bars
    if (reviews.length > 0) {
        const counts = [0,0,0,0,0];
        reviews.forEach(r=>counts[r.rating-1]++);
        let barsHtml = '';
        for(let i=5;i>=1;i--){
            const pct = reviews.length?Math.round(counts[i-1]/reviews.length*100):0;
            barsHtml+=`<div class="rating-bar-wrap">
                <span class="text-muted small" style="width:14px">${i}</span>
                <i class="bi bi-star-fill star-filled" style="font-size:.75rem"></i>
                <div class="rating-bar-bg"><div class="rating-bar-fill" style="width:${pct}%"></div></div>
                <span class="text-muted small" style="width:26px">${counts[i-1]}</span>
            </div>`;
        }
        $('#avgRatingBig').text(avgR.toFixed(1));
        $('#avgStars').html(stars(Math.round(avgR)));
        $('#reviewsCountLabel').text(`${reviewCount} отзывов`);
        $('#ratingBars').html(barsHtml);
    } else {
        $('#ratingsSummary').html('<p class="text-muted">Отзывов пока нет</p>');
    }

    // Reviews list
    const now = new Date();
    if (reviews.length) {
        $('#reviewsList').html(reviews.map(r=>{
            const letter = (r.author_name||'?')[0].toUpperCase();
            const dt = r.created_at ? new Date(r.created_at).toLocaleDateString('ru-RU',{day:'numeric',month:'long',year:'numeric'}) : '';
            return `<div class="review-card mb-3">
                <div class="d-flex gap-3 align-items-start">
                    <div class="avatar-circle">${letter}</div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-bold">${esc(r.author_name)}</span>
                            <small class="text-muted">${dt}</small>
                        </div>
                        <div class="mb-2">${stars(r.rating,'')}</div>
                        <p class="mb-0 text-muted">${esc(r.comment||'')}</p>
                    </div>
                </div>
            </div>`;
        }).join(''));
    } else {
        $('#reviewsList').html('<p class="text-muted">Будьте первым, кто оставит отзыв!</p>');
    }

    // Show page
    $('#pageLoader').hide();
    $('#pageContent').show();
    document.title = item.name + ' — BRONIC.RU';
}

// Date inputs — today and tomorrow default
const urlParams = new URLSearchParams(window.location.search);
let checkinParam = urlParams.get('checkin');
let checkoutParam = urlParams.get('checkout');

function formatDate(date) {
    let d = date.getDate(),
        m = date.getMonth() + 1,
        y = date.getFullYear();
    return `${d < 10 ? '0' + d : d}.${m < 10 ? '0' + m : m}.${y}`;
}

const today = new Date(), tomorrow = new Date(today);
tomorrow.setDate(today.getDate()+2);

let finalCheckin = checkinParam || formatDate(new Date());
let finalCheckout = checkoutParam || formatDate(tomorrow);

$('#checkinDate').val(finalCheckin);
$('#checkoutDate').val(finalCheckout);

// Инициализация datepicker для property.php (так как он тут тоже нужен)
if ($.fn.datepicker) {
    $("#checkinDate").datepicker({
        dateFormat: "dd.mm.yy",
        minDate: 0,
        onSelect: function(selected) {
            let min = $.datepicker.parseDate('dd.mm.yy', selected);
            min.setDate(min.getDate() + 2);
            $('#checkoutDate').datepicker('option', 'minDate', min);
            updatePriceBreakdown();
        }
    });
    $("#checkoutDate").datepicker({
        dateFormat: "dd.mm.yy",
        minDate: 2,
        onSelect: updatePriceBreakdown
    });
}

// Price breakdown
function updatePriceBreakdown() {
    if (!propertyData) return;
    
    let ciVal = $('#checkinDate').val();
    let coVal = $('#checkoutDate').val();
    
    if (ciVal && coVal) {
        try {
            const ci = $.datepicker.parseDate('dd.mm.yy', ciVal);
            const co = $.datepicker.parseDate('dd.mm.yy', coVal);
            const nights = Math.round((co-ci)/(86400000));
            if (nights > 0) {
                const pricePerNight = propertyData.base_price;
                const total = pricePerNight * nights;
                $('#pricePerNightLabel').text(`${Number(pricePerNight).toLocaleString('ru-RU')} ₽ × ${nights} ночей`);
                $('#pricePerNightTotal').text(`${Number(total).toLocaleString('ru-RU')} ₽`);
                $('#priceTotal').text(`${Number(total).toLocaleString('ru-RU')} ₽`);
                $('#priceBreakdown').removeClass('d-none');
            } else {
                $('#priceBreakdown').addClass('d-none');
            }
        } catch(e) {
            $('#priceBreakdown').addClass('d-none');
        }
    }
}
$('#checkinDate, #checkoutDate').on('change', updatePriceBreakdown);

// Book button
$('#bookBtn').on('click', function() {
    if (!propertyData) return;
    const ci = $('#checkinDate').val();
    const co = $('#checkoutDate').val();
    
    // Получаем гостей из URL если они там были
    const adults = urlParams.get('adults') || 2;
    const children = urlParams.get('children') || 0;
    
    window.location = `booking.php?id=${propertyData.id}&name=${encodeURIComponent(propertyData.name)}&price=${propertyData.base_price}&location=${encodeURIComponent(propertyData.address||propertyData.location||'')}&checkin=${ci}&checkout=${co}&adults=${adults}&children=${children}`;
});

// Star selector
let selectedRating = 5;
$('#starSelector').on('mouseenter','.star-select-btn',function(){
    const v=$(this).data('v');
    $('#starSelector .star-select-btn').each(function(i){ $(this).toggleClass('active',i<v); });
}).on('mouseleave',function(){
    const v=selectedRating;
    $('#starSelector .star-select-btn').each(function(i){ $(this).toggleClass('active',i<v); });
}).on('click','.star-select-btn',function(){
    selectedRating=$(this).data('v');
    $('#reviewRating').val(selectedRating);
    $('#starSelector .star-select-btn').each(function(i){ $(this).toggleClass('active',i<selectedRating); });
});
// Set default 5 stars
$('#starSelector .star-select-btn').addClass('active');

// Review submit
$('#reviewForm').on('submit', function(e){
    e.preventDefault();
    const btn=$(this).find('button[type=submit]');
    btn.prop('disabled',true).html('<span class="spinner-border spinner-border-sm me-2"></span>Отправка...');
    $.ajax({
        url:API+'/reviews', method:'POST', contentType:'application/json',
        data:JSON.stringify({resource_id:PID,author_name:$('#reviewName').val(),rating:parseInt($('#reviewRating').val()),comment:$('#reviewComment').val()}),
        success(r){
            if(r.success){
                // Вставляем новый отзыв сверху без перезагрузки
                const letter=($('#reviewName').val()||'?')[0].toUpperCase();
                const newCard=`<div class="review-card mb-3" style="animation:fadeUp .4s ease">
                    <div class="d-flex gap-3 align-items-start">
                        <div class="avatar-circle">${letter}</div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-1"><span class="fw-bold">${esc($('#reviewName').val())}</span><small class="text-muted">Только что</small></div>
                            <div class="mb-2">${stars(selectedRating)}</div>
                            <p class="mb-0 text-muted">${esc($('#reviewComment').val())}</p>
                        </div>
                    </div></div>`;
                $('#reviewsList').prepend(newCard);
                $('#reviewForm')[0].reset();
                selectedRating=5;
                $('#starSelector .star-select-btn').addClass('active');
                btn.prop('disabled',false).html('<i class="bi bi-send me-2"></i>Отправить отзыв');
                btn.closest('.review-form-wrap').css('border-color','#22c55e');
                setTimeout(()=>btn.closest('.review-form-wrap').css('border-color','#fce7eb'),2000);
            } else {
                alert(r.error||'Ошибка');
                btn.prop('disabled',false).html('<i class="bi bi-send me-2"></i>Отправить отзыв');
            }
        },
        error(){
            alert('Ошибка сервера');
            btn.prop('disabled',false).html('<i class="bi bi-send me-2"></i>Отправить отзыв');
        }
    });
});

// Load data
if (PID) {
    $.when($.getJSON(API+'/resources/'+PID), $.getJSON(API+'/reviews/'+PID))
     .done(function(ir,rr){ renderPage(ir[0], rr[0].reviews||[]); })
     .fail(function(){ $('#pageLoader').html('<div class="container"><div class="alert alert-danger text-center mt-5">Объект не найден. <a href="index.php" class="text-danger">На главную</a></div></div>'); });
} else {
    $('#pageLoader').html('<div class="container"><div class="alert alert-warning text-center mt-5">Укажите ID объекта. <a href="index.php" class="text-warning">На главную</a></div></div>');
}

// ========== 11. ПОКАЗАТЬ ТЕЛЕФОН ==========
$(document).on('click', '.btn-show-phone', function (e) {
    e.preventDefault();
    var $btn = $(this);
    if ($btn.data('phone-visible') === true) {
        $btn.html('<i class="bi bi-telephone me-2"></i>Показать телефон').removeClass('btn-success').addClass('btn-outline-secondary');
        $btn.data('phone-visible', false);
    } else {
        $btn.html('<i class="bi bi-telephone me-2"></i>+7 (495) 123-45-67').removeClass('btn-outline-secondary').addClass('btn-success');
        $btn.data('phone-visible', true);
    }
});
</script>
</body>
</html>
