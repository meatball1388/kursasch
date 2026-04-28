<h5>Варианты размещения</h5>
<form id="filterForm" action="filter.php" method="GET">
    <ul class="list-unstyled">
        <li>
            <div class="form-group">
                <input type="checkbox" name="property[]" id="ch1" value="appartment">
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
            <input type="text" id="minPrice" value="0" min="0">
            <span class="currency">₽</span>
        </div>
        <span class="separator">—</span>
        <div class="input-field">
            <input type="number" id="maxPrice" value="50000" min="0">
            <span class="currency">₽</span>
            <span class="plus">+</span>
        </div>
    </div>

    <div id="slider-range"></div>

    <hr>
    <button type="submit" class="btn btn-success w-100">
        <i class="bi bi-search me-2"></i>Отправить
    </button>
</form>