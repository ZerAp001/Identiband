<footer class="py-5 border-top border-secondary" style="background-color: var(--identi-dark);">
    <div class="container px-5">
        <div class="row align-items-center">

            <!-- Información de CONTACTO -->
            <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">

                <h5 class="text-white fw-bold mb-3">
                    CONTACTO
                </h5>

                <p class="text-white-50 mb-1">
                    <i class="bi bi-envelope-fill me-2" style="color: var(--identi-cyan);"></i>
                    contacto@identiband.com
                </p>

                <p class="text-white-50">
                    <i class="bi bi-whatsapp me-2" style="color: var(--identi-cyan);"></i>
                    +52 783 110 6301
                </p>

                <div class="small text-white-50 mt-4">
                    &copy; 2026 IDENTIBAND. Todos los derechos reservados.
                </div>

            </div>

            <!-- Información REDES SOCIALES -->
            <div class="col-md-6 text-center text-md-end">

                <h5 class="text-white fw-bold mb-3">
                    SÍGUENOS
                </h5>

                <div class="d-flex justify-content-center justify-content-md-end gap-3">

                    <!-- FACEBOOK -->
                    <a href="https://www.facebook.com/share/1HJrgrjkZS/" target="_blank" class="fs-3 text-white-50 hover-cyan" title="Facebook">
                        <i class="bi bi-facebook"></i>
                    </a>

                    <!-- INSTAGRAM -->
                    <a href="https://www.instagram.com/identi_band?igsh=OHp2czdkdWVoaHZk" target="_blank" class="fs-3 text-white-50 hover-cyan" title="Instagram">
                        <i class="bi bi-instagram"></i>
                    </a>

                    <!-- TWITTER -->
                    <a href="https://x.com/Identi_band" target="_blank" class="fs-3 text-white-50 hover-cyan" title="Twitter">
                        <i class="bi bi-twitter"></i>
                    </a>

                </div>

            </div>

        </div>
    </div>
</footer>

<!-- WIDGET FLOTANTE DE ATENCIÓN Y SOPORTE -->
<div class="floating-support-container">
    <!-- Botón FAQ -->
    <button type="button" class="btn btn-dark border-info text-info shadow-lg rounded-circle fab-btn" data-bs-toggle="modal" data-bs-target="#modalSoporte" title="Centro de Ayuda">
        <i class="bi bi-question-circle-fill fs-4"></i>
    </button>

    <!-- Botón Directo WhatsApp -->
    <a href="https://wa.me/527831106301?text=Hola!%20Tengo%20una%20consulta%20sobre%20mi%20pedido%20en%20IDENTIBAND" target="_blank" class="btn btn-success shadow-lg rounded-circle fab-btn ms-2" title="Soporte por WhatsApp">
        <i class="bi bi-whatsapp fs-4"></i>
    </a>
</div>

<!-- MODAL DE SOPORTE Y PREGUNTAS FRECUENTES (FAQ) -->
<div class="modal fade" id="modalSoporte" tabindex="-1" aria-labelledby="modalSoporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark border-secondary text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title text-info fw-bold" id="modalSoporteLabel">
                    <i class="bi bi-headset me-2"></i>Centro de Ayuda & Soporte
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="accordion accordion-flush" id="accordionSoporte">
                    
                    <!-- Duda 1 -->
                    <div class="accordion-item bg-dark text-white border-secondary">
                        <h2 class="accordion-header" id="faqOne">
                            <button class="accordion-button collapsed bg-dark text-info fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne">
                                ¿Cómo configuro el chip NFC de mi pulsera?
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionSoporte">
                            <div class="accordion-body text-white-50 small">
                                Puedes programar tu pulsera IDENTIBAND fácilmente usando aplicaciones gratuitas como <strong>NFC Tools</strong> (disponible en iOS y Android). Solo escanea tu pulsera y añade el enlace a tu red social o contacto.
                            </div>
                        </div>
                    </div>

                    <!-- Duda 2 -->
                    <div class="accordion-item bg-dark text-white border-secondary">
                        <h2 class="accordion-header" id="faqTwo">
                            <button class="accordion-button collapsed bg-dark text-info fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo">
                                ¿Cuánto tardan los envíos?
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionSoporte">
                            <div class="accordion-body text-white-50 small">
                                Los envíos estándar toman de 3 a 5 días hábiles. Recuerda que si tu compra supera el monto mínimo configurado, ¡el envío es totalmente gratis!
                            </div>
                        </div>
                    </div>

                    <!-- Duda 3 -->
                    <div class="accordion-item bg-dark text-white border-secondary">
                        <h2 class="accordion-header" id="faqThree">
                            <button class="accordion-button collapsed bg-dark text-info fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree">
                                ¿Requiere batería la pulsera NFC?
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionSoporte">
                            <div class="accordion-body text-white-50 small">
                                No, las pulseras cuentan con tecnología pasiva que no requiere batería ni recargas. Funcionan al contacto con un smartphone compatible.
                            </div>
                        </div>
                    </div>

                </div>

                <div class="text-center mt-4 pt-3 border-top border-secondary">
                    <p class="small text-white-50 mb-2">¿Necesitas ayuda personalizada?</p>
                    <a href="https://wa.me/527831106301?text=Hola!%20Necesito%20atención%20personalizada%20con%20un%20asesor" target="_blank" class="btn btn-sm btn-success fw-bold">
                        <i class="bi bi-whatsapp me-1"></i> Hablar con un asesor
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-cyan:hover {
    color: var(--identi-cyan) !important;
    transition: 0.3s;
}

/* ESTILOS BOTONES FLOTANTES DE SOPORTE */
.floating-support-container {
    position: fixed;
    bottom: 25px;
    right: 25px;
    z-index: 9999;
    display: flex;
    align-items: center;
}

.fab-btn {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.fab-btn:hover {
    transform: scale(1.1);
}

.accordion-button:not(.collapsed) {
    background-color: #161b22 !important;
    color: var(--identi-primary) !important;
}

.accordion-button::after {
    filter: invert(1);
}
</style>

<!-- Librería Bootstrap 5 para JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>