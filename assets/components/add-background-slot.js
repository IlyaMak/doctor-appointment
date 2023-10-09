export default function addBackgroundSlot() {
  const linkClassName = 'addSlotLink';

  function removeSlotLinks() {
    const links = document.querySelectorAll(`.${linkClassName}`);
    links.forEach((link) => link.remove());
  }

  function addBackgroundSlot(e) {
    removeSlotLinks();

    const link = document.createElement('a');
    link.className = linkClassName;

    const hour = e.target.dataset.hour;
    const date = e.target.dataset.date;
    const formSlotPath = e.target.dataset.formSlotPath;
    const startMinutes = Math.floor(e.offsetY / (e.target.clientHeight / 4)) * 15;

    const linkValue = `${formSlotPath}?date=${date}&hour=${hour}&startMinutes=${startMinutes}`;
    link.href = linkValue;
    const slotArea = document.createElement('div');
    slotArea.style.height = `calc(100% * (15 / 5 * (2.5/30)) - 2px)`;
    slotArea.style.top = `calc(100% * (( ${startMinutes} / 5 * (2.5/30)))`;

    slotArea.className = 'bg-success bg-opacity-75 rounded-3 text-white slot position-absolute';

    const smallText = document.createElement('small');
    smallText.innerHTML = '+ ' + hour.substring(0, 2) + ':' + (startMinutes.toString().padStart(2, '0'));
    smallText.className = 'pe-none';

    slotArea.prepend(smallText);
    slotArea.addEventListener('mouseout', () => link.remove());
    link.prepend(slotArea);
    e.target.parentElement.prepend(link);
  }

  const cells = document.querySelectorAll('.hourCell');
  cells.forEach((cell) => {
    cell.addEventListener('mousemove', addBackgroundSlot);
  });
}