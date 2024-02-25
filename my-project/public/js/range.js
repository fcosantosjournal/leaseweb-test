const hddSizesDiv = document.getElementById('hdd-sizes');
const sizes = JSON.parse(hddSizesDiv.dataset.sizes);

const range = document.getElementById('hdd_range');
const range_selected = document.getElementById('range_selected');
range.oninput = function() {
    if (this.value == 0) {
        range_selected.innerHTML = 'Select HDD Size';
        document.getElementById('hdd').value = '';
        return;
    }
    range_selected.innerHTML = sizes[this.value];
    document.getElementById('hdd').value = sizes[this.value];
}   