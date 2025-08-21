
// Dados dos produtos (normalmente viriam de uma API)
let products = [];
let cart = JSON.parse(localStorage.getItem('cart')) || [];

// DOM Elements
const productGrid = document.getElementById('product-grid');
const filterBtns = document.querySelectorAll('.filter-btn');
const cartCount = document.getElementById('cart-count');
const cartBtn = document.getElementById('cart-btn');
const cartSidebar = document.getElementById('cart-sidebar');
const closeCart = document.querySelector('.close-cart');
const cartItems = document.getElementById('cart-items');
const cartTotalPrice = document.getElementById('cart-total-price');
const checkoutBtn = document.getElementById('checkout-btn');
const searchBtn = document.getElementById('search-btn');
const searchBar = document.getElementById('search-bar');
const productModal = document.getElementById('product-modal');
const closeModal = document.querySelector('.close-modal');

// Carregar produtos do JSON
fetch('produtos.json')
    .then(response => response.json())
    .then(data => {
        products = data;
        displayProducts(products);
    })
    .catch(error => console.error('Erro ao carregar produtos:', error));

// Mostrar produtos
function displayProducts(productsToShow) {
    productGrid.innerHTML = '';
    
    productsToShow.forEach(product => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.dataset.category = product.category;
        
        productCard.innerHTML = `
            <div class="product-image">
                <img src="imagens/produtos/${product.image}" alt="${product.name}">
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <p class="product-price">R$ ${product.price.toFixed(2)}</p>
                <div class="product-rating">
                    ${getRatingStars(product.rating)}
                </div>
                <button class="add-to-cart" data-id="${product.id}">Adicionar ao Carrinho</button>
                <button class="view-details" data-id="${product.id}">Ver Detalhes</button>
            </div>
        `;
        
        productGrid.appendChild(productCard);
    });
    
    // Adicionar event listeners aos botões
    document.querySelectorAll('.add-to-cart').forEach(btn => {
        btn.addEventListener('click', addToCart);
    });
    
    document.querySelectorAll('.view-details').forEach(btn => {
        btn.addEventListener('click', showProductDetails);
    });
}

// Mostrar estrelas de avaliação
function getRatingStars(rating) {
    let stars = '';
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 >= 0.5;
    
    for (let i = 0; i < fullStars; i++) {
        stars += '<i class="fas fa-star"></i>';
    }
    
    if (hasHalfStar) {
        stars += '<i class="fas fa-star-half-alt"></i>';
    }
    
    const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
    for (let i = 0; i < emptyStars; i++) {
        stars += '<i class="far fa-star"></i>';
    }
    
    return stars;
}

// Filtrar produtos
filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
        // Remover classe active de todos os botões
        filterBtns.forEach(b => b.classList.remove('active'));
        // Adicionar classe active ao botão clicado
        btn.classList.add('active');
        
        const filter = btn.dataset.filter;
        
        if (filter === 'todos') {
            displayProducts(products);
        } else {
            const filteredProducts = products.filter(
                product => product.category === filter
            );
            displayProducts(filteredProducts);
        }
    });
});

// Adicionar ao carrinho
function addToCart(e) {
    const productId = parseInt(e.target.dataset.id);
    const product = products.find(p => p.id === productId);
    
    // Verificar se o produto já está no carrinho
    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            ...product,
            quantity: 1
        });
    }
    
    updateCart();
    showNotification(`${product.name} foi adicionado ao carrinho!`);
}

// Mostrar detalhes do produto
function showProductDetails(e) {
    const productId = parseInt(e.target.dataset.id);
    const product = products.find(p => p.id === productId);
    
    document.getElementById('modal-product-img').src = `imagens/produtos/${product.image}`;
    document.getElementById('modal-product-name').textContent = product.name;
    document.getElementById('modal-product-price').textContent = `R$ ${product.price.toFixed(2)}`;
    document.getElementById('modal-product-description').textContent = product.description;
    document.getElementById('product-qty').value = 1;
    
    // Configurar botão "Adicionar ao Carrinho" no modal
    const addToCartBtn = document.getElementById('add-to-cart-btn');
    addToCartBtn.dataset.id = product.id;
    addToCartBtn.onclick = function() {
        const quantity = parseInt(document.getElementById('product-qty').value);
        
        const existingItem = cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            cart.push({
                ...product,
                quantity: quantity
            });
        }
        
        updateCart();
        productModal.style.display = 'none';
        showNotification(`${product.name} (${quantity}x) foi adicionado ao carrinho!`);
    };
    
    // Configurar botões de quantidade
    document.getElementById('increase-qty').onclick = function() {
        const qtyInput = document.getElementById('product-qty');
        qtyInput.value = parseInt(qtyInput.value) + 1;
    };
    
    document.getElementById('decrease-qty').onclick = function() {
        const qtyInput = document.getElementById('product-qty');
        if (parseInt(qtyInput.value) > 1) {
            qtyInput.value = parseInt(qtyInput.value) - 1;
        }
    };
    
    productModal.style.display = 'block';
}

// Atualizar carrinho
function updateCart() {
    // Salvar carrinho no localStorage
    localStorage.setItem('cart', JSON.stringify(cart));
    
    // Atualizar contador do carrinho
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
    cartCount.textContent = totalItems;
    
    // Atualizar lista de itens no carrinho sidebar
    renderCartItems();
    
    // Atualizar total
    updateCartTotal();
}

// Renderizar itens do carrinho
function renderCartItems() {
    cartItems.innerHTML = '';
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<p>Seu carrinho está vazio.</p>';
        return;
    }
    
    cart.forEach(item => {
        const cartItem = document.createElement('div');
        cartItem.className = 'cart-item';
        
        cartItem.innerHTML = `
            <div class="cart-item-img">
                <img src="imagens/produtos/${item.image}" alt="${item.name}">
            </div>
            <div class="cart-item-details">
                <h4 class="cart-item-name">${item.name}</h4>
                <p class="cart-item-price">R$ ${item.price.toFixed(2)}</p>
                <div class="cart-item-qty">
                    <button class="decrease-item" data-id="${item.id}">-</button>
                    <input type="number" value="${item.quantity}" min="1" class="item-qty" data-id="${item.id}">
                    <button class="increase-item" data-id="${item.id}">+</button>
                </div>
                <p class="remove-item" data-id="${item.id}">Remover</p>
            </div>
        `;
        
        cartItems.appendChild(cartItem);
    });
    
    // Adicionar event listeners
    document.querySelectorAll('.decrease-item').forEach(btn => {
        btn.addEventListener('click', updateCartItemQuantity);
    });
    
    document.querySelectorAll('.increase-item').forEach(btn => {
        btn.addEventListener('click', updateCartItemQuantity);
    });
    
    document.querySelectorAll('.item-qty').forEach(input => {
        input.addEventListener('change', updateCartItemQuantity);
    });
    
    document.querySelectorAll('.remove-item').forEach(btn => {
        btn.addEventListener('click', removeCartItem);
    });
}

// Atualizar quantidade do item no carrinho
function updateCartItemQuantity(e) {
    const productId = parseInt(e.target.dataset.id);
    const item = cart.find(item => item.id === productId);
    
    if (e.target.classList.contains('decrease-item')) {
        if (item.quantity > 1) {
            item.quantity -= 1;
        }
    } else if (e.target.classList.contains('increase-item')) {
        item.quantity += 1;
    } else if (e.target.classList.contains('item-qty')) {
        const newQty = parseInt(e.target.value);
        if (newQty > 0) {
            item.quantity = newQty;
        }
    }
    
    updateCart();
}

// Remover item do carrinho
function removeCartItem(e) {
    const productId = parseInt(e.target.dataset.id);
    cart = cart.filter(item => item.id !== productId);
    updateCart();
}

// Atualizar total do carrinho
function updateCartTotal() {
 } const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    cartTotalPrice.textContent = `R; 
{
    };