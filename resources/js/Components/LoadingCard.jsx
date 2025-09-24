import React from 'react';
import LoadingSpinner from './LoadingSpinner';

const LoadingCard = ({ 
    message = 'Ma\'lumotlar yuklanmoqda...', 
    showSkeleton = true,
    skeletonLines = 3 
}) => {
    if (showSkeleton) {
        return (
            <div className="bg-white shadow-sm sm:rounded-lg p-6">
                <div className="animate-pulse">
                    <div className="flex items-center space-x-4 mb-4">
                        <div className="rounded-full bg-gray-300 h-10 w-10"></div>
                        <div className="space-y-2 flex-1">
                            <div className="h-4 bg-gray-300 rounded w-3/4"></div>
                            <div className="h-3 bg-gray-300 rounded w-1/2"></div>
                        </div>
                    </div>
                    {Array.from({ length: skeletonLines }).map((_, index) => (
                        <div key={index} className="space-y-2 mb-3">
                            <div className="h-3 bg-gray-300 rounded"></div>
                            <div className="h-3 bg-gray-300 rounded w-5/6"></div>
                        </div>
                    ))}
                </div>
            </div>
        );
    }

    return (
        <div className="bg-white shadow-sm sm:rounded-lg p-6">
            <LoadingSpinner 
                size="lg" 
                text={message}
                className="py-8"
            />
        </div>
    );
};

export default LoadingCard; 