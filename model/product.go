package model

import (
	"time"

	"github.com/google/uuid"
	"gorm.io/gorm"
)

type Product struct {
	ID        string     `gorm:"type:char(36);primaryKey" json:"id"`
	ClientID  string     `gorm:"type:char(36);not null" json:"client_id"`
	Name      string     `gorm:"type:varchar(255)" json:"name"`
	Status    int        `gorm:"type:int;default:1" json:"status"`
	CreatedAt time.Time  `json:"created_at"`
	UpdatedAt time.Time  `json:"updated_at"`
	Campaigns []Campaign `gorm:"foreignKey:ProductID" json:"campaigns"`
}

func (p *Product) BeforeCreate(tx *gorm.DB) (err error) {
	if p.ID == "" {
		p.ID = uuid.NewString()
	}
	return
}
