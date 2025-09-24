import React, { useState, useRef, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { MoreVertical } from 'lucide-react';

const DropdownActions = ({ children, className = '' }) => {
    const [isOpen, setIsOpen] = useState(false);
    const containerRef = useRef(null);
    const buttonRef = useRef(null);
    const menuRef = useRef(null);
    const [position, setPosition] = useState({ top: 0, left: 0, width: 0 });

    useEffect(() => {
        const handleClickOutside = (event) => {
            const target = event.target;
            const insideTrigger = containerRef.current && containerRef.current.contains(target);
            const insideMenu = menuRef.current && menuRef.current.contains(target);
            if (!insideTrigger && !insideMenu) {
                setIsOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);
        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    // Reposition on open/resize/scroll
    useEffect(() => {
        const updatePosition = () => {
            if (!buttonRef.current) return;
            const rect = buttonRef.current.getBoundingClientRect();
            const viewportWidth = window.innerWidth;
            const menuWidth = 192; // w-48
            const left = Math.min(rect.left, Math.max(0, viewportWidth - menuWidth - 8));
            setPosition({ top: rect.bottom + 8, left, width: rect.width });
        };

        if (isOpen) {
            updatePosition();
            window.addEventListener('resize', updatePosition);
            window.addEventListener('scroll', updatePosition, true);
            return () => {
                window.removeEventListener('resize', updatePosition);
                window.removeEventListener('scroll', updatePosition, true);
            };
        }
    }, [isOpen]);

    const toggleDropdown = () => {
        setIsOpen(!isOpen);
    };

    return (
        <div className={`relative inline-block text-left ${className}`} ref={containerRef}>
            {/* Settings tugmasi */}
            <button
                type="button"
                onClick={toggleDropdown}
                ref={buttonRef}
                className="inline-flex items-center justify-center h-8 w-8 rounded-full bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 hover:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 shadow-sm transition-colors duration-150 ease-in-out"
                aria-expanded={isOpen}
                aria-haspopup="true"
            >
                <MoreVertical className="w-4 h-4" />
            </button>

            {/* Dropdown menu */}
            {isOpen && createPortal(
                (
                    <div
                        ref={menuRef}
                        style={{ position: 'fixed', top: position.top, left: position.left, zIndex: 9999 }}
                        className="w-48 origin-top-right rounded-md bg-white shadow-xl border border-gray-200 focus:outline-none animate-in fade-in-0 zoom-in-95"
                    >
                        <div className="py-1">
                            {React.Children.map(children, (child, index) => (
                                <div key={index} className="block">
                                    {child}
                                </div>
                            ))}
                        </div>
                    </div>
                ),
                document.body
            )}
        </div>
    );
};

export default DropdownActions;
