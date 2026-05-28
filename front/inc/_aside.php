<h5>Варианты размещения</h5>
<form id="filterForm" action="filter.php" method="GET">
    <ul class="list-unstyled">
        <li>
            <div class="form-group">
                <input type="checkbox" name="property[]" id="ch1" value="apartment">
                <label for="ch1">Квартира</label>
            </div>
        </li>
        <li>
            <div class="form-group">
                <input type="checkbox" name="property[]" id="ch2" value="dacha">
                <label for="ch2">Дача</label>
            </div>
        </li>
        <li>
            <div class="form-group">
                <input type="checkbox" name="property[]" id="ch3" value="room">
                <label for="ch3">Комната</label>
            </div>
        </li>
        <li>
            <div class="form-group">
                <input type="checkbox" name="property[]" id="ch4" value="cottedzh">
                <label for="ch4">Коттедж</label>
            </div>
        </li>
    </ul>

    <h6 class="mt-4 mb-3">Цена за сутки</h6>

    <div class="price-inputs mb-3">
        <div class="input-field">
            <input type="text" id="minPrice" name="minPrice" value="0" min="0">
            <span class="currency">₽</span>
        </div>
        <span class="separator">—</span>
        <div class="input-field">
            <input type="number" id="maxPrice" name="maxPrice" value="50000" min="0">
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
                <input type="checkbox" name="amenities[]" id="am1" value="wifi">
                <label for="am1">Wi-Fi</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="checkbox" name="amenities[]" id="am2" value="parking">
                <label for="am2">Парковка</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="checkbox" name="amenities[]" id="am3" value="ac">
                <label for="am3">Кондиционер</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="checkbox" name="amenities[]" id="am4" value="kitchen">
                <label for="am4">Кухня</label>
            </div>
        </li>
    </ul>

    <h6 class="mb-3">Рейтинг</h6>
    <ul class="list-unstyled mb-4">
        <li class="mb-2">
            <div class="form-group">
                <input type="radio" name="rating" id="r5" value="5">
                <label for="r5"><i class="bi bi-star-fill text-warning me-1"></i> 5 звезд</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="radio" name="rating" id="r4" value="4">
                <label for="r4"><i class="bi bi-star-fill text-warning me-1"></i> 4+ звезды</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="radio" name="rating" id="r3" value="3">
                <label for="r3"><i class="bi bi-star-fill text-warning me-1"></i> 3+ звезды</label>
            </div>
        </li>
        <li class="mb-2">
            <div class="form-group">
                <input type="radio" name="rating" id="rany" value="any" checked>
                <label for="rany">Любой</label>
            </div>
        </li>
    </ul>

    <button type="submit" class="btn btn-success w-100">
        <i class="bi bi-search me-2"></i>Отправить
    </button>
</form>