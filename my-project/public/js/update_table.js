var formData
        
document.getElementById('filter-btn').addEventListener('click', function() {
    var form = document.getElementById('form')
    var formData = new FormData(form)
    var xhr = new XMLHttpRequest()
    xhr.open('POST', '/api/filter-servers', true)
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest')
    xhr.onload = function() {
        if (xhr.status === 200) {
            var servers = JSON.parse(xhr.responseText)
            updateServersTable(servers)
        } else {
            console.error('Request failed. Status: ' + xhr.status)
        }
    }
    xhr.onerror = function() {
        console.error('Request failed')
    }
    xhr.send(formData)
})

function getOriginalColumnOrderByColumnName() {
    var originalColumnOrder = []
    var originalColumnOrder = document.getElementById('servers-table').getElementsByTagName('thead')[0].getElementsByTagName('th')
    var columnsReturn = [];
    for (var i = 0; i < originalColumnOrder.length; i++) {
        columnsReturn[i] = originalColumnOrder[i].textContent
        columnsReturn[i] = columnsReturn[i].replace(/\s/g, '_')
    }
    return columnsReturn
}

function updateServersTable(servers) {
    var tableBody = document.getElementById('servers-table').getElementsByTagName('tbody')[0]
    tableBody.innerHTML = ''
    originalColumnOrder = getOriginalColumnOrderByColumnName()
    for (var i = 1; i < servers.length; i++) {
        var server = servers[i]
        var row = document.createElement('tr')
        originalColumnOrder.forEach(function(column) {
            var cell = document.createElement('td')
            cell.textContent = server[column]
            row.appendChild(cell)
        })
        tableBody.appendChild(row)
    }
}