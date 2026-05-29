<!-- Product Reviews Partial View - displays reviews with reply functionality for sellers/admins -->
@if($reviews && count($reviews) > 0)
<div class="reviews-section">
    <h3 style="font-size: 18px; font-weight: 700; color: #1e293b; margin-bottom: 20px;">
        Customer Reviews ({{ count($reviews) }})
    </h3>

    @foreach($reviews as $review)
    <div class="review-item" style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 16px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
        <!-- Review Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
            <div style="display: flex; gap: 12px; align-items: flex-start; flex: 1;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #64748b; font-weight: 700; flex-shrink: 0;">
                    {{ strtoupper(substr($review->user->name, 0, 1)) }}
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: #1e293b; font-size: 15px;">{{ $review->user->name }}</div>
                    <div style="font-size: 13px; color: #64748b; margin-top: 2px;">{{ $review->created_at->diffForHumans() }}</div>
                </div>
            </div>
            <div style="display: flex; gap: 4px; color: #fbbf24;">
                @for($i = 0; $i < $review->rating; $i++)
                    <span>★</span>
                @endfor
                @for($i = $review->rating; $i < 5; $i++)
                    <span style="color: #cbd5e1;">★</span>
                @endfor
            </div>
        </div>

        <!-- Review Title and Comment -->
        @if($review->title)
        <div style="font-weight: 600; color: #1e293b; margin-bottom: 8px; font-size: 15px;">{{ $review->title }}</div>
        @endif
        <div style="color: #475569; font-size: 14px; line-height: 1.6; margin-bottom: 12px;">{{ $review->comment }}</div>

        <!-- Reply Section -->
        @if($review->reply)
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; margin-top: 12px;">
            <div style="font-weight: 600; color: #16a34a; margin-bottom: 8px; font-size: 13px;">
                ✓ Seller Reply
            </div>
            <div style="color: #475569; font-size: 14px; line-height: 1.6; margin-bottom: 6px;">
                {{ $review->reply }}
            </div>
            <div style="font-size: 12px; color: #64748b;">
                Replied by {{ $review->repliedByUser?->name ?? 'Seller' }} • {{ $review->replied_at?->diffForHumans() }}
            </div>
        </div>
        @elseif(auth()->check() && (auth()->user()->id === $product->seller_id || auth()->user()->id === $product->admin_id || auth()->user()->role === 'admin'))
        <!-- Reply Form for Authorized Users -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-top: 12px;">
            <button onclick="toggleReplyForm({{ $review->id }})" style="background: #4f46e5; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">
                Reply to Review
            </button>

            <div id="reply-form-{{ $review->id }}" style="display: none; margin-top: 12px;">
                <textarea id="reply-textarea-{{ $review->id }}" placeholder="Write your reply..." style="width: 100%; padding: 10px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px; font-family: inherit; resize: vertical; min-height: 80px; margin-bottom: 10px;"></textarea>
                
                <div style="display: flex; gap: 8px; justify-content: space-between;">
                    <button onclick="openEmojiModal(document.getElementById('reply-textarea-{{ $review->id }}'))" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 8px 12px; border-radius: 4px; cursor: pointer; font-size: 16px;">
                        😊
                    </button>
                    <div style="display: flex; gap: 10px;">
                        <button onclick="toggleReplyForm({{ $review->id }})" style="background: #e2e8f0; color: #1e293b; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">
                            Cancel
                        </button>
                        <button onclick="submitReply({{ $review->id }})" style="background: #4f46e5; color: white; border: none; padding: 8px 16px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 13px;">
                            Post Reply
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endforeach
</div>

<script>
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

    if (reply.length > 1000) {
        alert('Reply must be 1000 characters or less');
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

const emojis = ['😀', '😂', '😍', '🤔', '👍', '👏', '✨', '🎉', '❤️', '🔥', '💯', '🎊', '👌', '😊', '😌', '😃', '😄', '😁', '🤩', '😇', '🤑', '😎', '🥳', '😋', '😘', '😚', '😗', '🤗', '🤭', '🤫', '🤥', '😔', '🤐', '🤨', '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '😌'];

let currentReplyTextarea = null;

function openEmojiModal(textarea) {
    currentReplyTextarea = textarea;
    let emojiModal = document.getElementById('emojiModal');
    if (!emojiModal) {
        emojiModal = document.createElement('div');
        emojiModal.id = 'emojiModal';
        emojiModal.style.cssText = 'display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; border-radius: 12px; padding: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); z-index: 1000; max-width: 400px;';
        emojiModal.innerHTML = '<h3 style="margin-bottom: 15px;">Select Emoji</h3><div id="emojiGrid" style="display: grid; grid-template-columns: repeat(6, 1fr); gap: 10px; max-height: 300px; overflow-y: auto;"></div><button onclick="closeEmojiModal()" style="margin-top: 15px; width: 100%; padding: 10px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer;">Close</button>';
        document.body.appendChild(emojiModal);
        
        const emojiGrid = document.getElementById('emojiGrid');
        emojis.forEach(emoji => {
            const btn = document.createElement('button');
            btn.textContent = emoji;
            btn.style.cssText = 'padding: 10px; font-size: 20px; border: 1px solid #e2e8f0; border-radius: 6px; cursor: pointer; background: white;';
            btn.onclick = () => insertEmoji(emoji);
            emojiGrid.appendChild(btn);
        });
    }
    
    let overlay = document.getElementById('emojiOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'emojiOverlay';
        overlay.style.cssText = 'display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.3); z-index: 999;';
        overlay.onclick = closeEmojiModal;
        document.body.appendChild(overlay);
    }
    
    emojiModal.style.display = 'block';
    overlay.style.display = 'block';
}

function closeEmojiModal() {
    const modal = document.getElementById('emojiModal');
    const overlay = document.getElementById('emojiOverlay');
    if (modal) modal.style.display = 'none';
    if (overlay) overlay.style.display = 'none';
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
</script>

@else
<div style="text-align: center; padding: 40px 20px; color: #64748b; background: #f8fafc; border-radius: 8px;">
    <p style="font-size: 15px;">No reviews yet. Be the first to review this product!</p>
</div>
@endif
