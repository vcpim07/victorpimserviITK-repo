package model

import (
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type ProductShared struct {
	ID        string    `gorm:"type:char(36);primaryKey" json:"id"`
	ProductID string    `gorm:"type:char(36);not null" json:"product_id"`
	UserID    string    `gorm:"type:char(36);not null" json:"user_id"`
	Status    int       `gorm:"type:int;default:1" json:"status"`
	CreatedAt time.Time `json:"created_at"`
}

func (ps *ProductShared) BeforeCreate(tx *gorm.DB) (err error) {
	if ps.ID == "" {
		ps.ID = uuid.NewString()
	}
	return
}
