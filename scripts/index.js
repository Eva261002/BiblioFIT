
        // Pequeña animación para las tarjetas
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card-hover');
            
            cards.forEach((card, index) => {
                // Animación de aparición escalonada
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.animation = `fadeInUp 0.5s ease forwards ${index * 0.1}s`;
                
                // Efecto hover mejorado
                card.addEventListener('mouseenter', function() {
                    this.style.boxShadow = '0 15px 30px rgba(0, 0, 0, 0.1)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.boxShadow = '0 4px 6px rgba(0, 0, 0, 0.1)';
                });
            });
        });
