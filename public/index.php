<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Supermercado</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Minha Lista</h1>
        
        <div class="input-group" style="flex-direction: column;">
            <input type="text" id="item-input" placeholder="O que comprar?" autocomplete="off">
            <div style="display: flex; gap: 0.5rem;">
                <input type="text" id="packaging-input" placeholder="Embalagem (ex: 1kg, 2L)" style="flex: 2;">
                <input type="number" id="price-input" placeholder="Preço" step="0.01" style="flex: 1; min-width: 0; background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 0.75rem; padding: 0.75rem 1rem; color: var(--text); outline: none;">
                <button id="add-btn">Add</button>
            </div>
        </div>

        <ul id="grocery-list">
            <!-- Items will be loaded here -->
        </ul>
    </div>

    <div class="modal-overlay" id="edit-modal">
        <div class="modal">
            <h2>Editar Item</h2>
            <input type="hidden" id="edit-id">
            <div class="form-group">
                <label>Nome</label>
                <input type="text" id="edit-name">
            </div>
            <div class="form-group">
                <label>Embalagem</label>
                <input type="text" id="edit-packaging">
            </div>
            <div class="form-group">
                <label>Preço Médio</label>
                <input type="number" id="edit-price" step="0.01">
            </div>
            <div class="modal-actions">
                <button class="btn btn-cancel" onclick="closeModal()">Cancelar</button>
                <button class="btn btn-save" onclick="saveEdit()">Salvar</button>
            </div>
        </div>
    </div>

    <script>
        const listElement = document.getElementById('grocery-list');
        const inputElement = document.getElementById('item-input');
        const packagingInput = document.getElementById('packaging-input');
        const priceInput = document.getElementById('price-input');
        const addBtn = document.getElementById('add-btn');

        let currentItems = [];

        async function fetchItems() {
            const response = await fetch('api.php');
            currentItems = await response.json();
            renderList(currentItems);
        }

        function renderList(items) {
            if (items.length === 0) {
                listElement.innerHTML = '<div class="empty-state">Sua lista está vazia.</div>';
                return;
            }

            listElement.innerHTML = items.map(item => `
                <li data-id="${item.id}">
                    <div style="flex: 1;">
                        <span class="item-text ${item.completed ? 'completed' : ''}" onclick="toggleItem(${item.id})">
                            ${item.name}
                        </span>
                        <div class="item-details">
                            ${item.packaging ? `<span>${item.packaging}</span>` : ''}
                            ${item.average_price ? `<span class="price-tag">R$ ${parseFloat(item.average_price).toFixed(2)}</span>` : ''}
                        </div>
                    </div>
                    <div style="display: flex;">
                        <button class="edit-btn" onclick="openEditModal(${item.id})">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </button>
                        <button class="delete-btn" onclick="deleteItem(${item.id})">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"></path><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                    </div>
                </li>
            `).join('');
        }

        async function addItem() {
            const name = inputElement.value.trim();
            const packaging = packagingInput.value.trim();
            const average_price = priceInput.value.trim();

            if (!name) return;

            const response = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    action: 'add', 
                    name,
                    packaging: packaging || null,
                    average_price: average_price || null
                })
            });

            if (response.ok) {
                inputElement.value = '';
                packagingInput.value = '';
                priceInput.value = '';
                fetchItems();
            }
        }

        function openEditModal(id) {
            const item = currentItems.find(i => i.id == id);
            if (!item) return;

            document.getElementById('edit-id').value = item.id;
            document.getElementById('edit-name').value = item.name;
            document.getElementById('edit-packaging').value = item.packaging || '';
            document.getElementById('edit-price').value = item.average_price || '';
            document.getElementById('edit-modal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('edit-modal').style.display = 'none';
        }

        async function saveEdit() {
            const id = document.getElementById('edit-id').value;
            const name = document.getElementById('edit-name').value.trim();
            const packaging = document.getElementById('edit-packaging').value.trim();
            const average_price = document.getElementById('edit-price').value.trim();

            if (!name) return;

            const response = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    action: 'edit', 
                    id,
                    name,
                    packaging: packaging || null,
                    average_price: average_price || null
                })
            });

            if (response.ok) {
                closeModal();
                fetchItems();
            }
        }

        async function toggleItem(id) {
            await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'toggle', id })
            });
            fetchItems();
        }

        async function deleteItem(id) {
            if (!confirm('Tem certeza que deseja excluir?')) return;
            
            await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'delete', id })
            });
            fetchItems();
        }

        addBtn.addEventListener('click', addItem);
        inputElement.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') addItem();
        });

        // Initial load
        fetchItems();
    </script>
</body>
</html>
