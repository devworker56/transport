        </main>
        
        <!-- Footer -->
        <footer class="bg-dark text-white py-4 mt-5">
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <h5>TransportGabon</h5>
                        <p>Plateforme collaborative de livraison pour connecter les agriculteurs et les transporteurs au Gabon.</p>
                    </div>
                    <div class="col-md-4">
                        <h5>Liens rapides</h5>
                        <ul class="list-unstyled">
                            <li><a href="<?php echo SITE_URL; ?>" class="text-white">Accueil</a></li>
                            <li><a href="<?php echo SITE_URL; ?>#how-it-works" class="text-white">Comment ça marche</a></li>
                            <li><a href="<?php echo SITE_URL; ?>#contact" class="text-white">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5>Contact</h5>
                        <p><i class="fas fa-envelope"></i> contact@transportgabon.ga</p>
                        <p><i class="fas fa-phone"></i> +241 XX XX XX XX</p>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <p>&copy; <?php echo date('Y'); ?> TransportGabon. Tous droits réservés.</p>
                </div>
            </div>
        </footer>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        
        <!-- Custom JS -->
        <script src="<?php echo SITE_URL; ?>/js/script.js"></script>
    </body>
</html>
<?php
// Flush output buffer
ob_end_flush();
?>