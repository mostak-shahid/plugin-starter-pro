import { FaStar, FaStarHalfAlt, FaRegStar } from "react-icons/fa";

export default function FontAwesomeRating({rating}) {
    // Convert your 0-100 score to a 0-5 scale
    const numericRating = parseFloat((rating / 20).toFixed(2));

    return (
        <div className="d-flex align-items-center gap-1 text-warning" aria-label={`Rating: ${numericRating} out of 5`}>
            {[1, 2, 3, 4, 5].map((starIndex) => {
                if (numericRating >= starIndex) {
                    // Full Star
                    return <FaStar key={starIndex} className="star-rating solid-full-star"  />;
                } else if (numericRating > starIndex - 1 && numericRating < starIndex) {
                    // Half Star
                    return <FaStarHalfAlt key={starIndex} className="star-rating half-full-star"  />;
                } else {
                    // Empty Star
                    return <FaRegStar key={starIndex} className="star-rating border-full-star" />;
                }
            })}
            <span className="d-none ms-1 text-muted small">{numericRating}</span>
        </div>
    );
};

// Usage:
// <FontAwesomeRating rating={rating} />

