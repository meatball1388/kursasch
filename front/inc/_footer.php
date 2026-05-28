<footer class="bg-light border-top mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h5>Соцсети</h5>
                <div class="d-flex gap-3">
                    <a href="https://vk.com" target="_blank" class="text-decoration-none text-secondary d-flex align-items-center gap-1"><i class="bi bi-chat-dots"></i>ВК</a>
                    <a href="https://t.me" target="_blank" class="text-decoration-none text-secondary d-flex align-items-center gap-1"><i class="bi bi-telegram"></i>Телеграмм</a>
                    <a href="https://ok.ru" target="_blank" class="text-decoration-none text-secondary d-flex align-items-center gap-1"><i class="bi bi-people"></i>Одноклассники</a>
                    <a href="https://instagram.com" target="_blank" class="text-decoration-none text-secondary d-flex align-items-center gap-1"><i class="bi bi-instagram"></i>Инстаграм</a>
                </div>
                <p class="small text-muted mt-2">Подписывайтесь на наши соцсети</p>
            </div>
            <div class="col-md-4">
                <h5>Подписка</h5>
                <p class="small text-muted">Ежемесячный обзор новинок и интересных событий от нас.</p>
                <form id="subscribeForm" class="input-group" onsubmit="handleSubscribe(event)">
                    <input type="email" class="form-control" id="subscribeEmail" placeholder="Почта" required>
                    <button class="btn btn-outline-secondary" type="submit">Подписаться</button>
                </form>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <p class="text-muted">© 2026 Bronic, Inc. Все права защищены.</p>
            </div>
        </div>
    </div>
</footer>

<script>
function handleSubscribe(e) {
    e.preventDefault();
    const email = document.getElementById('subscribeEmail').value;
    if(email) {
        alert('Спасибо! Адрес ' + email + ' успешно подписан на рассылку новостей.');
        document.getElementById('subscribeEmail').value = '';
    }
}
</script>