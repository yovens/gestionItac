<?php
// includes/footer.php
?>
  </main>
  <footer class="footer">
    <div>© ITAC - Institut Technologie Appliquée des cayes</div>
  </footer>
</body>
</html>


<style>
  .footer {
  background: #2c3e50;   /* Bleu foncé professionnel */
  color: #ecf0f1;        /* Texte clair */
  text-align: center;
  padding: 15px 10px;
  font-size: 14px;
  margin-top: 40px;
  border-top: 3px solid #f1c40f; /* Ligne jaune or */
  animation: fadeInUp 0.8s ease-in-out;
}

.footer div {
  letter-spacing: 0.5px;
}

/* Animation */
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

/* Responsive */
@media (max-width: 600px) {
  .footer {
    font-size: 12px;
    padding: 12px 8px;
  }
}

</style>