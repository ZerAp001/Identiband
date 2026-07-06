        </div>

    </main>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

document.addEventListener('DOMContentLoaded', () => {

    // Elimina backdrops pegados
    document.querySelectorAll('.modal-backdrop').forEach(el => {
        el.remove();
    });

    // Limpia clases del body
    document.body.classList.remove('modal-open');

    // Restaura scroll
    document.body.style.overflow = 'auto';
    document.body.style.paddingRight = '0px';
});

</script>

</body>
</html>