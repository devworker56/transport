        </main>
        
        <!-- Footer with reduced height -->
        <footer class="bg-dark text-white py-2 mt-5"> <!-- Changed from py-4 to py-2 -->
            <div class="container">
                <div class="row">
                    <div class="col-md-4">
                        <h5 class="mb-1">TransportGabon</h5> <!-- Added mb-1 for tighter spacing -->
                        <p class="mb-2 small">Plateforme collaborative de livraison pour connecter les agriculteurs et les transporteurs au Gabon.</p> <!-- Added mb-2 and small class -->
                    </div>
                    <div class="col-md-4">
                        <h5 class="mb-1">Liens rapides</h5> <!-- Added mb-1 -->
                        <ul class="list-unstyled mb-2"> <!-- Added mb-2 -->
                            <li><a href="<?php echo SITE_URL; ?>" class="text-white">Accueil</a></li>
                            <li><a href="<?php echo SITE_URL; ?>#how-it-works" class="text-white">Comment ça marche</a></li>
                            <li><a href="<?php echo SITE_URL; ?>#contact" class="text-white">Contact</a></li>
                        </ul>
                    </div>
                    <div class="col-md-4">
                        <h5 class="mb-1">Contact</h5> <!-- Added mb-1 -->
                        <p class="mb-1 small"><i class="fas fa-envelope"></i> contact@transportgabon.ga</p> <!-- Added mb-1 and small class -->
                        <p class="mb-2 small"><i class="fas fa-phone"></i> +241 XX XX XX XX</p> <!-- Added mb-2 and small class -->
                        <div class="d-flex gap-2"> <!-- Reduced gap from 3 to 2 -->
                            <a href="#" class="text-white"><i class="fab fa-facebook fa-lg"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-twitter fa-lg"></i></a>
                            <a href="#" class="text-white"><i class="fab fa-instagram fa-lg"></i></a>
                        </div>
                    </div>
                </div>
                <hr class="my-2"> <!-- Reduced margin from default to my-2 -->
                <div class="text-center">
                    <p class="mb-0 small">&copy; <?php echo date('Y'); ?> TransportGabon. Tous droits réservés.</p> <!-- Added mb-0 and small class -->
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