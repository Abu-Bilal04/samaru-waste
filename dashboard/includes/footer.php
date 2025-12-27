    </div> <!-- End Main Content -->

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle Sidebar
        const toggleBtn = document.getElementById('menuToggle');
        if(toggleBtn){
            toggleBtn.addEventListener('click', function() {
                document.getElementById('sidebar').classList.toggle('active');
            });
        }
    </script>
</body>
</html>
