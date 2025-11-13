package model

import (
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type Campaign struct {
	ID        string    `gorm:"type:char(36);primaryKey" json:"id"`
	ProductID string    `gorm:"type:char(36);not null" json:"product_id"`
	Name      string    `gorm:"type:varchar(255)" json:"name"`
	Status    int       `gorm:"type:int;default:1" json:"status"`
	CreatedAt time.Time `json:"created_at"`
	UpdatedAt time.Time `json:"updated_at"`
}

func (c *Campaign) BeforeCreate(tx *gorm.DB) (err error) {
	if c.ID == "" {
		c.ID = uuid.NewString()
	}
	return
}
