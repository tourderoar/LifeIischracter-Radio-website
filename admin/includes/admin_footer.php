            </main>
        </div>
    </div>
    
    <script>
        // Add any common admin JavaScript here
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success messages after 5 seconds
            const successAlerts = document.querySelectorAll('.alert-success');
            successAlerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>
