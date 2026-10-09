const addIngredientButton = document.getElementById('addIngredient')
const ingredientsContainer = document.getElementById('ingredients')

if (addIngredientButton && ingredientsContainer) {
    addIngredientButton.addEventListener('click', function () {
        const input = document.createElement('input')

        input.type = 'text'
        input.name = 'ingredients[]'
        input.required = true
        input.maxLength = 255
        input.placeholder = 'Another ingredient'

        ingredientsContainer.appendChild(input)
    })
}

const favoriteButton = document.querySelector('.favorite-button')

if (favoriteButton) {
    favoriteButton.addEventListener('click', async function () {
        const recipeId = favoriteButton.dataset.recipeId

        const formData = new FormData()
        formData.append('recipe_id', recipeId)

        const response = await fetch('toggle_favorite.php', {
            method: 'POST',
            body: formData
        })

        const result = await response.json()

        if (result.success) {
            favoriteButton.textContent = result.favorite
                ? 'Remove Favorite'
                : 'Save Favorite'
        }
    })
}