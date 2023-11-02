export default function () {
    function addGuideImageSrc(e) {
        const increasedImage = document.querySelector('.increasedImage');
        increasedImage.src = e.target.src;
    }

    const guideImages = document.querySelectorAll('.guideImage');
    guideImages.forEach((el) => {
        el.addEventListener('click', addGuideImageSrc);
    });
}