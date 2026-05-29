@extends('layouts.admin')

@section('title', 'Product Reviews & Replies')

@section('styles')
<style>
    .reviews-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 30px 20px;
    }

    .reviews-header {
        margin-bottom: 30px;
    }

    .reviews-header h1 {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 10px;
    }

    .reviews-search {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .reviews-search input {
        flex: 1;
        padding: 10px 15px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
    }

    .reviews-search button {
        padding: 10px 20px;
        background: #4f46e5;
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }

    .review-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 15px;
    }

    .review-user-info {
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .review-user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #64748b;
    }

    .review-user-details {
        flex: 1;
    }

    .review-user-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 15px;
    }

    .review-date {
        font-size: 13px;
        color: #64748b;
    }

    .review-rating {
        display: flex;
        gap: 3px;
        align-items: center;
    }

    .review-star {
        color: #fbbf24;
        font-size: 16px;
    }

    .review-title {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
        font-size: 15px;
    }

    .review-comment {
        color: #475569;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 12px;
    }

    .reply-section {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 15px;
        margin-top: 15px;
    }

    .reply-exists {
        background: #f0fdf4;
        border-left: 4px solid #16a34a;
    }

    .reply-header {
        font-weight: 600;
        color: #16a34a;
        margin-bottom: 10px;
        font-size: 13px;
    }

    .reply-text {
        color: #475569;
        font-size: 14px;
        line-height: 1.6;
        margin-bottom: 8px;
    }

    .reply-by {
        font-size: 12px;
        color: #64748b;
    }

    .reply-form textarea {
        width: 100%;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        resize: vertical;
        min-height: 80px;
        margin-bottom: 10px;
    }

    .reply-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .reply-btn {
        padding: 8px 16px;
        background: #4f46e5;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
    }

    .reply-btn:hover {
        background: #4338ca;
    }

    .cancel-btn {
        background: #e2e8f0;
        color: #1e293b;
    }

    .cancel-btn:hover {
        background: #cbd5e1;
    }

    .emoji-picker-btn {
        background: #f1f5f9;
        color: #475569;
        border: 1px solid #e2e8f0;
        padding: 6px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 18px;
    }

    .no-reviews {
        text-align: center;
        padding: 60px 20px;
        color: #64748b;
    }

    .filter-group {
        margin-bottom: 20px;
    }

    .filter-label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 8px;
        display: block;
        font-size: 14px;
    }

    .filter-select {
        width: 100%;
        padding: 10px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
    }

    @media (max-width: 768px) {
        .reviews-search {
            flex-direction: column;
        }

        .review-header {
            flex-direction: column;
            gap: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="reviews-container">
    <div class="reviews-header">
        <h1>Product Reviews & Seller Replies</h1>
        <p style="color: #64748b; margin-top: 5px;">Manage product reviews and respond to customer feedback</p>
    </div>

    <div class="reviews-search">
        <input type="text" id="searchInput" placeholder="Search by product name or customer name...">
        <button onclick="filterReviews()">Search</button>
    </div>

    <div class="filter-group" style="margin-top: 20px;">
        <label class="filter-label">Filter by Status</label>
        <select class="filter-select" id="statusFilter" onchange="filterReviews()">
            <option value="">All Reviews</option>
            <option value="replied">With Replies</option>
            <option value="pending">Pending Replies</option>
        </select>
    </div>

    <div id="reviewsList" style="margin-top: 30px;">
        <!-- Reviews will be loaded here -->
    </div>
</div>

<!-- Emoji Picker Modal -->
<div id="emojiModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 12px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 1000; max-width: 400px;">
    <h3 style="margin-bottom: 15px;">Select Emoji</h3>
    <div id="emojiGrid" style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; max-height: 300px; overflow-y: auto;">
        <!-- Emojis will be loaded here -->
    </div>
    <button onclick="closeEmojiModal()" style="margin-top: 15px; width: 100%; padding: 10px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer;">Close</button>
</div>

<div id="emojiOverlay" onclick="closeEmojiModal()" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); z-index: 999;"></div>

@endsection

@section('scripts')
<script>
    const emojis = ['😀', '😂', '😍', '🤔', '👍', '👏', '✨', '🎉', '❤️', '🔥', '💯', '🎊', '👌', '😊', '😌', '😃', '😄', '😁', '🤩', '😇', '🤑', '😎', '🥳', '😋', '😘', '😚', '😗', '😚', '🤗', '🤭', '🤫', '🤥', '😌', '😔', '😔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥', '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕', '🤮', '🤢', '🤮', '🤮'];

    let currentReplyTextarea = null;
    let products = [];

    document.addEventListener('DOMContentLoaded', function() {
        loadReviews();
        loadEmojis();
    });

    function loadEmojis() {
        const emojiGrid = document.getElementById('emojiGrid');
        emojiGrid.innerHTML = '';
        emojis.forEach(emoji => {
            const button = document.createElement('button');
            button.textContent = emoji;
            button.style.cssText = 'padding: 10px; font-size: 24px; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; background: white;';
            button.onclick = () => insertEmoji(emoji);
            emojiGrid.appendChild(button);
        });
    }

    function insertEmoji(emoji) {
        if (currentReplyTextarea) {
            const start = currentReplyTextarea.selectionStart;
            const end = currentReplyTextarea.selectionEnd;
            const text = currentReplyTextarea.value;
            currentReplyTextarea.value = text.substring(0, start) + emoji + text.substring(end);
            currentReplyTextarea.focus();
        }
        closeEmojiModal();
    }

    function openEmojiModal(textarea) {
        currentReplyTextarea = textarea;
        document.getElementById('emojiModal').style.display = 'block';
        document.getElementById('emojiOverlay').style.display = 'block';
    }

    function closeEmojiModal() {
        document.getElementById('emojiModal').style.display = 'none';
        document.getElementById('emojiOverlay').style.display = 'none';
    }

    async function loadReviews() {
        try {
            const response = await fetch('{{ route("admin.products") }}');
            // Since we need product reviews, we'll fetch them differently
            // For now, show a message
            const reviewsList = document.getElementById('reviewsList');
            reviewsList.innerHTML = `
                <div class="no-reviews">
                    <p>Review management feature - navigate to individual products to view and reply to reviews.</p>
                    <p style="font-size: 13px; margin-top: 10px; color: #94a3b8;">Visit the Products page and select a product to see customer reviews and add replies.</p>
                </div>
            `;
        } catch (error) {
            console.error('Error loading reviews:', error);
        }
    }

    function filterReviews() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        
        const reviewCards = document.querySelectorAll('.review-card');
        reviewCards.forEach(card => {
            let show = true;

            if (searchTerm) {
                const text = card.textContent.toLowerCase();
                show = text.includes(searchTerm);
            }

            if (show && statusFilter) {
                const hasReply = card.querySelector('.reply-exists') !== null;
                if (statusFilter === 'replied' && !hasReply) show = false;
                if (statusFilter === 'pending' && hasReply) show = false;
            }

            card.style.display = show ? 'block' : 'none';
        });
    }

    function toggleReplyForm(reviewId) {
        const form = document.getElementById(`reply-form-${reviewId}`);
        if (form.style.display === 'none') {
            form.style.display = 'block';
            form.querySelector('textarea').focus();
        } else {
            form.style.display = 'none';
        }
    }

    async function submitReply(reviewId) {
        const textarea = document.getElementById(`reply-textarea-${reviewId}`);
        const reply = textarea.value.trim();

        if (!reply) {
            alert('Please enter a reply');
            return;
        }

        try {
            const response = await fetch(`/review/${reviewId}/reply`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ reply }),
            });

            const data = await response.json();

            if (data.success) {
                alert('Reply posted successfully!');
                location.reload();
            } else {
                alert(data.message || 'Failed to post reply');
            }
        } catch (error) {
            console.error('Error submitting reply:', error);
            alert('Error submitting reply');
        }
    }
</script>
@endsection
