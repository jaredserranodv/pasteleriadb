function loadProducts(){
    fetch('obtener_productos.php')
        .then(response => response.json())
        .then(products => {

            const productList= document.getElementById('product-list');
            productList.innerHTML = '';

            products.forEach(product => {

                const productDiv = document.createElement('div');
                productDiv.classList.add('product');
                productDiv.setAttribute('data-id', product.id);

                const img = document.createElement('img');
                img.src = `images/${product.imagen}`;
                img.alt = product.nombre;
                productDiv.appendChild(img);

                const name = document.createElement('h3');
                name.textContent = product.nombre;
                productDiv.appendChild(name);

                // Crear y agregar la categoría
                const categoria = document.createElement('span');
                categoria.classList.add('product-categoria');
                categoria.textContent = product.categoria;
                productDiv.appendChild(categoria);

                const cantidad = document.createElement('p');
                cantidad.classList.add('product-cantidad');
                cantidad.textContent = `Cantidad disponible: ${product.cantidad}`;
                productDiv.appendChild(cantidad);

                // Crear y agregar la descripción
                const descripcion = document.createElement('p');
                descripcion.classList.add('product-description');
                descripcion.textContent = product.descripcion;
                productDiv.appendChild(descripcion);
                
                const price = document.createElement('p');
                price.classList.add('price');
                price.textContent = `Precio: $${product.precio}`;
                productDiv.appendChild(price);

                const button = document.createElement('button');
                button.textContent = 'Añadir al carrito';
                button.classList.add('add-to-cart');
                button.addEventListener('click', () =>{
                    addToCart(product);
                });
                productDiv.appendChild(button);
                productList.appendChild(productDiv);
            });
        });
}


let cart = [];

function updateCart() {
    const cartItems = document.getElementById('cart-items');
    const cartTotal = document.getElementById('cart-total');

    cartItems.innerHTML = '';

    if(cart.length === 0) {
        cartItems.innerHTML = '<p>Tu carrito está vacío.</p>';
        cartTotal.textContent = '0';
        return;
    }

    let total = 0;
    cart.forEach(item => {
        const productDiv = document.createElement('div');
        const subtotal = item.precio * item.cantidad;
        productDiv.textContent = `${item.nombre} - $${item.precio} x ${item.cantidad} = $${subtotal.toFixed(2)}`;
        cartItems.appendChild(productDiv);
        total += subtotal;
    });

    cartTotal.textContent = total.toFixed(2);
}



function addToCart(product) {
    const existingItem = cart.find(item => item.id === product.id);
    if (existingItem) {
        existingItem.cantidad += 1; // Sumar cantidad
    } else {
        // Copiar el producto y añadir la propiedad cantidad=1
        cart.push({...product, cantidad: 1});
    }
    updateCart();
}


document.getElementById('checkout').addEventListener('click', () => {
    if (cart.length === 0) {
        alert('El carrito está vacío.');
        return;
    }

    // Mostrar formulario de pago
    document.getElementById('payment-form').style.display = 'block';
});

document.getElementById('metodoPago').addEventListener('change', (e) => {
    const tarjetaFields = document.getElementById('tarjeta-fields');
    if (e.target.value === 'efectivo') {
        tarjetaFields.style.display = 'none';
    } else {
        tarjetaFields.style.display = 'block';
    }
});

document.getElementById('confirmarCompra').addEventListener('click', (e) => {
    e.preventDefault(); // evita recarga

    console.log('Confirmar compra presionado');

    const metodoPago = document.getElementById('metodoPago').value;
    console.log('Método de pago:', metodoPago);

    let datosPago = {};
    if (metodoPago === 'tarjeta') {
        datosPago = {
            numeroTarjeta: document.getElementById('numeroTarjeta').value.trim(),
            nombreTarjeta: document.getElementById('nombreTarjeta').value.trim(),
            fechaExpiracion: document.getElementById('fechaExpiracion').value.trim(),
            cvv: document.getElementById('cvv').value.trim(),
        };

        console.log('Datos de tarjeta:', datosPago);

        if (!datosPago.numeroTarjeta || !datosPago.nombreTarjeta || !datosPago.fechaExpiracion || !datosPago.cvv) {
            alert('Por favor completa todos los campos de la tarjeta.');
            return;
        }
    }

    if (cart.length === 0) {
        alert('El carrito está vacío.');
        return;
    }

    console.log('Enviando carrito:', cart);

    fetch('procesar_compra.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            carrito: cart,
            metodo_pago: metodoPago,
            datos_pago: datosPago
        })
    })
    .then(response => {
        console.log('Respuesta recibida, status:', response.status);
        return response.text(); // Cambia a text() para ver el contenido crudo
    })
    .then(text => {
        console.log('Respuesta cruda:', text);
        try {
            const data = JSON.parse(text);
            console.log('Respuesta JSON:', data);
            alert(data.message);
            if (data.message === 'Compra procesada correctamente') {
                cart = [];
                updateCart();
                document.getElementById('payment-form').style.display = 'none';
            }
        } catch (e) {
            console.error('Error al parsear JSON:', e);
            alert('Respuesta inválida del servidor');
        }
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        alert('Error al procesar la compra');
    });
    
});





loadProducts();