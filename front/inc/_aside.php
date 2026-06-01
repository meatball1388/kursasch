<?php
$get_property = isset($_GET['property']) ? $_GET['property'] : [];
$get_amenities = isset($_GET['amenities']) ? $_GET['amenities'] : [];
$get_rating = isset($_GET['rating']) ? $_GET['rating'] : 'any';
$get_minPrice = isset($_GET['minPrice']) ? intval($_GET['minPrice']) : 0;
$get_maxPrice = isset($_GET['maxPrice']) ? intval($_GET['maxPrice']) : 20000;
?>
<h5>Варианты размещения</h5>
<form id="filterForm" action="filter.php" method="GET">
    <ul class="list-unstyled">
        <li>
            <div class="form-group">
                <input type="checkbox" name="property[]" id="ch1" value="apartment" <?php echo in_array('apartment', $get_property) ? 'checked' : ''; ?>>
                <label for="ch1">Квартира</label>
            </div>
        </li>
        <li>
            <div class="form-group">
                <input type="checkbox" name="property[]" id="ch2" value="dacha" <?php echo in_array('dacha', $get_property) ? 'checked' : ''; ?>>
                <label for="ch2">Дача</label>
            </div>
        </li>
        <li>
            <div class="form-group">
                <input type="checkbox" name="property[]" id="ch3" value="room" <?php echo in_array('room', $get_property) ? 'checked' : ''; ?>>
                <label for="ch3">Комната</label>
            </div>
        </li>
        <li>
            <div class="form-group">
                <input type="checkbox" name="property[]" id="ch4" value="cottedzh" <?php echo in_array('cottedzh', $get_property) ? 'checked' : ''; ?>>
                <label for="ch4">Коттедж</label>
            </div>
        </li>
    </ul>

    <h6 class="mt-4 mb-3">Цена за сутки</h6>

    <div class="price-inputs mb-3">
        <div class="input-field">
            <input type="text" id="minPrice" name="minPrice" value="<?php echo $get_minPrice; ?>" min="0">
            <span class="currency">₽</span>
        </div>
        <span class="separator">—</span>
        <div class="input-field">
            <input type="number" id="maxPrice" name="maxPrice" value="<?php echo $get_maxPrice; ?>" min="0">
            <span class="currency">₽</span>
            <span class="plus">+</span>
        </div>
    </div>

    <div id="slider-range"></div>

    <hr class="my-4">

    <h6 class="mb-3">Удобства</h6>
    <ul class="list-unstyled mb-4">
        <li class="mb-2">
            <div class="form-group">
                <input type="checkbox" name="amenities[]" id="am1" value="wifi" <?php echo in_array('wifi', $get_amenities) ? 'checked' : ''; ?>>
                <label for="am1">Wi-Fi</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="checkbox" name="amenities[]" id="am2" value="parking" <?php echo in_array('parking', $get_amenities) ? 'checked' : ''; ?>>
                <label for="am2">Парковка</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="checkbox" name="amenities[]" id="am3" value="ac" <?php echo in_array('ac', $get_amenities) ? 'checked' : ''; ?>>
                <label for="am3">Кондиционер</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="checkbox" name="amenities[]" id="am4" value="kitchen" <?php echo in_array('kitchen', $get_amenities) ? 'checked' : ''; ?>>
                <label for="am4">Кухня</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="checkbox" name="amenities[]" id="am6" value="tv" <?php echo in_array('tv', $get_amenities) ? 'checked' : ''; ?>>
                <label for="am6">ТВ</label>
            </div>
        </li>
    </ul>

    <h6 class="mb-3">Рейтинг</h6>
    <ul class="list-unstyled mb-4">
        <li class="mb-2">
            <div class="form-group">
                <input type="radio" name="rating" id="r5" value="5" <?php echo $get_rating == '5' ? 'checked' : ''; ?>>
                <label for="r5"><i class="bi bi-star-fill text-warning me-1"></i> 5 звезд</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="radio" name="rating" id="r4" value="4" <?php echo $get_rating == '4' ? 'checked' : ''; ?>>
                <label for="r4"><i class="bi bi-star-fill text-warning me-1"></i> 4+ звезды</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="radio" name="rating" id="r3" value="3" <?php echo $get_rating == '3' ? 'checked' : ''; ?>>
                <label for="r3"><i class="bi bi-star-fill text-warning me-1"></i> 3+ звезды</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="radio" name="rating" id="rany" value="any" <?php echo ($get_rating == 'any' || !$get_rating) ? 'checked' : ''; ?>>
                <label for="rany">Любой</label>
            </div>
        </li>
    </ul>

    <button type="submit" class="btn btn-success w-100">
        <i class="bi bi-search me-2"></i>Отправить
    </button>
</form>