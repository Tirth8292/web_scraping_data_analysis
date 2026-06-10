// Header Animations - Kept as is
gsap.from("#right-side", {
    y: -500,
    duration: 0.9,
    delay: 0.2
});

gsap.from("#mid-side", {
    y: -100,
    duration: 0.9,
    delay: 0.2
});

gsap.from("#left-side", {
    x: -500,
    duration: 0.9,
    delay: 0.2
});

// New Hero Slider Logic (Sliding Effect)
const sliderItems = document.querySelectorAll(".slider-bg-item");
let currentSlideIndex = 0;

if (sliderItems.length > 0) {
    function rotateSlide() {
        const nextSlideIndex = (currentSlideIndex + 1) % sliderItems.length;

        const currentSlide = sliderItems[currentSlideIndex];
        const nextSlide = sliderItems[nextSlideIndex];

        // Ensure next slide is visible but off-screen to the LEFT (for left-to-right effect)
        gsap.set(nextSlide, { x: "-100%", opacity: 1, zIndex: 1 });
        gsap.set(currentSlide, { zIndex: 0 });

        // Animate!
        // Animate!
        // Move next slide in from left to center (0%)
        gsap.to(nextSlide, {
            x: "0%",
            duration: 1.2,
            ease: "power2.inOut",
            onComplete: () => {
                // Cleanup
                currentSlide.classList.remove("active");
                nextSlide.classList.add("active");
                // Reset positions for future reuse
                gsap.set(currentSlide, { opacity: 0, x: 0 });
            }
        });

        // Move current slide to the right (push effect) so they don't stack
        gsap.to(currentSlide, {
            x: "100%",
            duration: 1.2,
            ease: "power2.inOut"
        });

        // Optionally move current slide to the right (parallax/push effect)
        // gsap.to(currentSlide, { x: "100%", duration: 1.2, ease: "power2.inOut" });

        currentSlideIndex = nextSlideIndex;
    }

    // Change slide every 5 seconds
    setInterval(rotateSlide, 5000);
}
