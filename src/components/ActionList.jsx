import React from 'react';
import { CheckCircle, Clock, ArrowRight } from 'lucide-react';
import './CardStyles.css';
import './ActionList.css';

const ActionList = ({ t }) => {
    const actions = [
        { id: 1, text: t('action_1'), icon: 'droplet' },
        { id: 2, text: t('action_2'), icon: 'search' },
    ];

    return (
        <div className="card-container">
            <h3 className="card-title">
                <CheckCircle size={18} color="#4CAF50" />
                {t('todays_action')}
            </h3>

            <div className="action-list">
                {actions.map((action) => (
                    <div key={action.id} className="action-item">
                        <div className="action-icon-bg">
                            <Clock size={16} color="#2196F3" />
                        </div>
                        <span className="action-text">{action.text}</span>
                        <ArrowRight size={16} color="#ccc" className="action-arrow" />
                    </div>
                ))}
            </div>
        </div>
    );
}; // Need styles for action-list, action-item
export default ActionList;
