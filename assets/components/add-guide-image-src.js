export default function () {
    function addGuideImageSrc(e) {
        const zoomedInImage = document.querySelector('.zoomedInImage');
        zoomedInImage.src = e.target.src;
    }

    const guideImages = document.querySelectorAll('.guideImage');
    guideImages.forEach((el) => {
        el.addEventListener('click', addGuideImageSrc);
    });
}